<?php


namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'saleItems.product']);

        // Date filter
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('sale_date', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Cashier filter (managers can see all, cashiers see only their own)
        if ($request->filled('user_id') && auth()->user()->role === 'manager') {
            $query->where('user_id', $request->user_id);
        } elseif (auth()->user()->role === 'cashier') {
            $query->where('user_id', auth()->id());
        }

        // Receipt number search
        if ($request->filled('receipt_number')) {
            $query->where('receipt_number', 'like', '%' . $request->receipt_number . '%');
        }

        $sales = $query->latest('sale_date')->paginate(20);

        // Get filters data
        $customers = Customer::active()->get();
        $users = auth()->user()->role === 'manager' ? User::active()->get() : collect();

        // Calculate totals for current filtered results
        $totalAmount = $query->sum('total_amount');
        $totalProfit = $query->get()->sum(function ($sale) {
            return $sale->saleItems->sum('profit');
        });

        return view('sales.index', compact(
            'sales',
            'customers',
            'users',
            'totalAmount',
            'totalProfit'
        ));
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale)
    {
        // Check permissions
        if (auth()->user()->role === 'cashier' && $sale->user_id !== auth()->id()) {
            abort(403, 'غير مسموح لك بعرض هذا الإيصال');
        }

        $sale->load(['customer', 'user', 'saleItems.product.category']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Display receipt for printing
     */
    public function receipt(Sale $sale)
    {
        // Check permissions
        if (auth()->user()->role === 'cashier' && $sale->user_id !== auth()->id()) {
            abort(403, 'غير مسموح لك بعرض هذا الإيصال');
        }

        $sale->load(['customer', 'user', 'saleItems.product']);

        return view('sales.receipt', compact('sale'));
    }

    /**
     * Get today's sales summary
     */
    public function todaysSummary()
    {
        $user = auth()->user();

        if ($user->role === 'cashier') {
            $sales = Sale::today()->byUser($user->id)->get();
        } else {
            $sales = Sale::today()->get();
        }

        $summary = [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('total_amount'),
            'total_profit' => $sales->sum(function ($sale) {
                return $sale->saleItems->sum('profit');
            }),
            'cash_sales' => $sales->where('payment_method', 'cash')->count(),
            'debt_sales' => $sales->where('payment_method', 'debt')->count(),
            'cash_amount' => $sales->where('payment_method', 'cash')->sum('total_amount'),
            'debt_amount' => $sales->where('payment_method', 'debt')->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }

    /**
     * Get sales analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 'today');

        $query = Sale::query();

        switch ($period) {
            case 'today':
                $query->whereDate('sale_date', today());
                break;
            case 'this_week':
                $query->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('sale_date', now()->month)
                    ->whereYear('sale_date', now()->year);
                break;
            case 'this_year':
                $query->whereYear('sale_date', now()->year);
                break;
        }

        // Filter by user role
        if (auth()->user()->role === 'cashier') {
            $query->where('user_id', auth()->id());
        }

        $sales = $query->with('saleItems')->get();

        $analytics = [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('total_amount'),
            'total_profit' => $sales->sum(function ($sale) {
                return $sale->saleItems->sum('profit');
            }),
            'average_sale' => $sales->count() > 0 ? $sales->avg('total_amount') : 0,
            'payment_methods' => [
                'cash' => [
                    'count' => $sales->where('payment_method', 'cash')->count(),
                    'amount' => $sales->where('payment_method', 'cash')->sum('total_amount'),
                ],
                'debt' => [
                    'count' => $sales->where('payment_method', 'debt')->count(),
                    'amount' => $sales->where('payment_method', 'debt')->sum('total_amount'),
                ],
            ],
            'top_products' => $this->getTopProducts($sales),
            'hourly_sales' => $this->getHourlySales($sales),
        ];

        return response()->json([
            'success' => true,
            'analytics' => $analytics
        ]);
    }

    /**
     * Void a sale (Manager only)
     */
    public function void(Request $request, Sale $sale)
    {
        if (auth()->user()->role !== 'manager') {
            return response()->json([
                'success' => false,
                'message' => 'غير مسموح لك بإلغاء المبيعات'
            ]);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            \DB::beginTransaction();

            // Restore stock
            foreach ($sale->saleItems as $item) {
                $item->product->increaseStock($item->quantity);
            }

            // Remove debt if debt sale
            if ($sale->payment_method === 'debt' && $sale->customer) {
                $sale->customer->decrement('total_debt', $sale->debt_amount);
            }

            // Mark as voided (we don't delete, just mark)
            $sale->update([
                'notes' => ($sale->notes ? $sale->notes . "\n" : '') .
                    "VOIDED: " . $request->reason . " by " . auth()->user()->name
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء البيع بنجاح'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء البيع'
            ]);
        }
    }

    /**
     * Get top selling products
     */
    private function getTopProducts($sales)
    {
        $products = [];

        foreach ($sales as $sale) {
            foreach ($sale->saleItems as $item) {
                $productId = $item->product_id;

                if (!isset($products[$productId])) {
                    $products[$productId] = [
                        'name' => $item->product->display_name,
                        'quantity' => 0,
                        'amount' => 0,
                    ];
                }

                $products[$productId]['quantity'] += $item->quantity;
                $products[$productId]['amount'] += $item->total_price;
            }
        }

        // Sort by quantity and return top 5
        uasort($products, function ($a, $b) {
            return $b['quantity'] <=> $a['quantity'];
        });

        return array_slice($products, 0, 5, true);
    }

    /**
     * Get hourly sales data
     */
    private function getHourlySales($sales)
    {
        $hourlyData = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyData[$hour] = [
                'hour' => sprintf('%02d:00', $hour),
                'count' => 0,
                'amount' => 0,
            ];
        }

        foreach ($sales as $sale) {
            $hour = $sale->sale_date->hour;
            $hourlyData[$hour]['count']++;
            $hourlyData[$hour]['amount'] += $sale->total_amount;
        }

        return array_values($hourlyData);
    }

    /**
     * Export sales to CSV
     */
    public function export(Request $request)
    {
        // This would be implemented for CSV/Excel export
        // For now, return a simple response
        return response()->json([
            'success' => false,
            'message' => 'التصدير غير متوفر حالياً'
        ]);
    }
}

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
        // If receipt_number is provided in the quick search (top form), redirect to show page
        if ($request->filled('receipt_number') && !$request->hasAny(['date_from', 'date_to', 'payment_method', 'customer_id', 'user_id'])) {
            $sale = Sale::where('receipt_number', trim($request->receipt_number))->first();

            if ($sale) {
                // Check permissions
                if (auth()->user()->role === 'cashier' && $sale->user_id !== auth()->id()) {
                    return redirect()->route('sales.index')
                        ->with('error', 'غير مسموح لك بعرض هذا الإيصال');
                }
                return redirect()->route('sales.show', $sale);
            }

            return redirect()->route('sales.index')
                ->with('error', "لم يتم العثور على بيع برقم الإيصال: {$request->receipt_number}");
        }

        $query = Sale::with(['customer', 'user', 'saleItems.product']);

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

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('user_id') && auth()->user()->role === 'manager') {
            $query->where('user_id', $request->user_id);
        } elseif (auth()->user()->role === 'cashier') {
            $query->where('user_id', auth()->id());
        }

        // For filter form, use LIKE search
        if ($request->filled('receipt_number') && $request->hasAny(['date_from', 'date_to', 'payment_method', 'customer_id', 'user_id'])) {
            $query->where('receipt_number', 'like', '%' . $request->receipt_number . '%');
        }

        $sales = $query->latest('sale_date')->paginate($request->get('per_page', 10));

        $customers = Customer::active()->get();
        $users = auth()->user()->role === 'manager' ? User::active()->get() : collect();

        $totalSalesCount = (clone $query)->notVoided()->count();
        $totalAmount = (clone $query)->notVoided()->get()->sum(function ($sale) {
            // For cash sales: total_amount (fully paid)
            // For debt sales: total_amount - debt_amount (amount actually received)
            if ($sale->payment_method === 'debt') {
                return $sale->total_amount - $sale->debt_amount;
            }
            return $sale->total_amount;
        });
        $totalProfit = (clone $query)->notVoided()->get()->sum(function ($sale) {
            return $sale->saleItems->sum('profit');
        });
        $voidedSalesCount = (clone $query)->where('is_voided', true)->count();

        return view('sales.index', compact(
            'sales',
            'customers',
            'users',
            'totalSalesCount',
            'totalAmount',
            'totalProfit',
            'voidedSalesCount'
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

        $sale->load(['customer', 'user', 'saleItems.product.category', 'returns.returnItems.product']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Display receipt for printing
     */
    public function receipt(Sale $sale)
    {
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
            $sales = Sale::today()->byUser($user->id)->notVoided()->get();
        } else {
            $sales = Sale::today()->notVoided()->get();
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

        if (auth()->user()->role === 'cashier') {
            $query->where('user_id', auth()->id());
        }

        $sales = $query->notVoided()->with('saleItems')->get();

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
            ], 403);
        }

        // Manual validation to return JSON on failure
        $validator = \Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إدخال سبب الإلغاء'
            ], 422);
        }

        if ($sale->is_voided) {
            return response()->json([
                'success' => false,
                'message' => 'هذا البيع ملغي بالفعل'
            ], 400);
        }

        // Get total paid before voiding
        $totalPaid = $sale->getTotalPaid();

        // Use new voidSale method
        if ($sale->voidSale($request->reason, auth()->id())) {
            $message = 'تم إلغاء البيع بنجاح';
            if ($totalPaid > 0) {
                $message .= ". يجب إرجاع مبلغ $" . number_format($totalPaid, 2) . " نقداً للعميل";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'total_refund' => $totalPaid
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء إلغاء البيع'
        ], 500);
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
        return response()->json([
            'success' => false,
            'message' => 'التصدير غير متوفر حالياً'
        ]);
    }

    /**
     * Lookup sale by barcode/receipt number for payment
     */
    public function lookupPayment(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $barcode = trim($request->barcode);

        // Find sale by receipt number
        $sale = Sale::where('receipt_number', $barcode)->first();

        if (!$sale) {
            return redirect()->route('sales.index')
                ->with('error', "لم يتم العثور على بيع بهذا الرقم: {$barcode}");
        }

        // Check if sale has a customer
        if (!$sale->customer) {
            return redirect()->route('sales.index')
                ->with('error', "هذا البيع ({$barcode}) ليس مرتبطاً بأي عميل");
        }

        // Check if it's a debt sale
        if ($sale->payment_method !== 'debt') {
            return redirect()->route('sales.index')
                ->with('error', "هذا البيع ({$barcode}) ليس بيع دين");
        }

        // Check if sale is voided
        if ($sale->is_voided) {
            return redirect()->route('sales.show', $sale)
                ->with('error', "هذا البيع ({$barcode}) ملغي");
        }

        // Check if there's remaining debt
        if ($sale->debt_amount <= 0) {
            return redirect()->route('sales.show', $sale)
                ->with('warning', "هذا البيع ({$barcode}) مدفوع بالكامل");
        }

        // Redirect to payment form
        return redirect()->route('debt.payment-form', [$sale->customer, $sale]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    /**
     * Display listing of returns with filters
     */
    public function index(Request $request)
    {
        $query = ReturnModel::with(['sale', 'user', 'returnItems.product']);

        // Date filter
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('return_date', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'voided') {
                $query->where('is_voided', true);
            } else {
                $query->where('is_voided', false);
            }
        }

        // User filter (cashiers see only their own)
        if (auth()->user()->role === 'cashier') {
            $query->where('user_id', auth()->id());
        }

        $returns = $query->latest('return_date')->paginate(20);

        // Calculate totals
        $totalReturnsCount = (clone $query)->notVoided()->count();
        $totalReturnAmount = (clone $query)->notVoided()->sum('total_return_amount');
        $totalCashRefund = (clone $query)->notVoided()->sum('cash_refund_amount');
        $totalDebtReduction = (clone $query)->notVoided()->sum('debt_reduction_amount');

        return view('returns.index', compact(
            'returns',
            'totalReturnsCount',
            'totalReturnAmount',
            'totalCashRefund',
            'totalDebtReduction'
        ));
    }

    /**
     * Show form for creating new return
     */
    public function create()
    {
        return view('returns.create');
    }

    /**
     * AJAX: Search for sale by receipt number
     *
     * Returns sale with items and their returnable quantities
     */
    public function searchSale(Request $request)
    {
        $request->validate([
            'receipt_number' => 'required|string',
        ]);

        // Trim and sanitize input
        $receiptNumber = trim($request->receipt_number);

        // Try exact match first
        $sale = Sale::where('receipt_number', $receiptNumber)
            ->with(['customer', 'saleItems.product', 'returns'])
            ->first();

        // If not found, try without dash (user might enter 202601110021 instead of 20260111-0021)
        if (!$sale && str_contains($receiptNumber, '-')) {
            $receiptNumberWithoutDash = str_replace('-', '', $receiptNumber);
            $sale = Sale::where('receipt_number', 'like', '%' . $receiptNumberWithoutDash . '%')
                ->with(['customer', 'saleItems.product', 'returns'])
                ->first();
        }

        // If still not found, try with partial match
        if (!$sale) {
            $sale = Sale::where('receipt_number', 'like', '%' . $receiptNumber . '%')
                ->with(['customer', 'saleItems.product', 'returns'])
                ->first();
        }

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على الإيصال: ' . $receiptNumber
            ], 404);
        }

        if ($sale->is_voided) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إرجاع عناصر من بيع ملغي'
            ], 400);
        }

        // Get returnable items with quantities
        $returnableItems = $sale->getReturnableItems();

        if ($returnableItems->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'جميع العناصر تم إرجاعها بالفعل'
            ], 400);
        }

        // Format items for frontend
        $items = $returnableItems->map(function ($item) {
            // Calculate the actual price per unit after discount
            $actualPricePerUnit = $item->total_price / $item->quantity;

            return [
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->display_name,
                'quantity_sold' => $item->quantity,
                'quantity_returned' => $item->quantity - $item->returnable_quantity,
                'quantity_available' => $item->returnable_quantity,
                'unit_price' => $actualPricePerUnit, // Send the actual price paid per unit
                'original_unit_price' => $item->unit_price, // Keep original for reference if needed
                'discount_amount' => $item->discount_amount,
            ];
        });

        return response()->json([
            'success' => true,
            'sale' => [
                'id' => $sale->id,
                'receipt_number' => $sale->receipt_number,
                'sale_date' => $sale->sale_date->format('Y-m-d'),
                'customer_name' => $sale->customer ? $sale->customer->name : 'زبون نقدي',
                'payment_method' => $sale->payment_method,
                'total_amount' => $sale->total_amount,
                'debt_amount' => $sale->debt_amount,
                'paid_amount' => $sale->total_amount - $sale->debt_amount,
            ],
            'items' => $items,
        ]);
    }

    /**
     * Store new return
     *
     * Expected JSON:
     * {
     *   "sale_id": 1,
     *   "reason": "damaged products",
     *   "items": [
     *     {"sale_item_id": 1, "quantity": 5},
     *     {"sale_item_id": 2, "quantity": 10}
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $sale = Sale::with(['customer', 'saleItems.product'])->findOrFail($request->sale_id);

        \Log::info('Processing return for sale', ['sale_id' => $sale->id, 'items' => $request->items]);

        // Process return using model method
        $return = ReturnModel::processReturn($sale, $request->items, $request->reason);

        if (!$return) {
            \Log::error('Return processing failed', ['sale_id' => $sale->id]);
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء معالجة الإرجاع')
                ->withInput();
        }

        \Log::info('Return created successfully', ['return_id' => $return->id, 'return_number' => $return->return_number]);

        return redirect()->route('returns.show', $return)
            ->with('success', 'تم معالجة الإرجاع بنجاح');
    }

    /**
     * Display return details
     */
    public function show(ReturnModel $return)
    {
        // Check permissions
        if (auth()->user()->role === 'cashier' && $return->user_id !== auth()->id()) {
            abort(403, 'غير مسموح لك بعرض هذا الإرجاع');
        }

        $return->load(['sale.customer', 'user', 'returnItems.product', 'voidedBy']);

        return view('returns.show', compact('return'));
    }

    /**
     * Display printable return receipt
     */
    public function receipt(ReturnModel $return)
    {
        // Check permissions
        if (auth()->user()->role === 'cashier' && $return->user_id !== auth()->id()) {
            abort(403, 'غير مسموح لك بعرض هذا الإيصال');
        }

        $return->load(['sale.customer', 'user', 'returnItems.product']);

        return view('returns.receipt', compact('return'));
    }

    /**
     * Void a return (Manager only)
     */
    public function void(Request $request, ReturnModel $return)
    {
        try {
            // Check manager permission
            if (auth()->user()->role !== 'manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسموح لك بإلغاء الإرجاعات'
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

            if ($return->is_voided) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الإرجاع ملغي بالفعل'
                ], 400);
            }

            // Use voidReturn method
            if ($return->voidReturn($request->reason, auth()->id())) {
                $message = 'تم إلغاء الإرجاع بنجاح';

                if ($return->cash_refund_amount > 0) {
                    $message .= ". تم استرداد مبلغ $" . number_format($return->cash_refund_amount, 2) . " نقداً للعميل سابقاً";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء الإرجاع'
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Void return exception: ' . $e->getMessage(), [
                'return_id' => $return->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء الإرجاع: ' . $e->getMessage()
            ], 500);
        }
    }
}

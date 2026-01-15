<?php

namespace App\Http\Controllers;

use App\Models\{Sale, SaleItem, Purchase, ReturnModel, Customer, Supplier, Product, Setting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Exports\{SalesExport, ProfitExport, DebtAgingExport, LowStockExport, ReturnsExport};

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'saleItems.product'])->notVoided();

        if ($request->filled('date_from')) $query->whereDate('sale_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('sale_date', '<=', $request->date_to);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);

        $sales = $query->latest('sale_date')->paginate(20);
        $totalSalesCount = (clone $query)->count();
        $totalRevenue = (clone $query)->sum('total_amount');
        $totalCashCollected = (clone $query)->where('payment_method', 'cash')->sum('total_amount');
        $totalDebtCreated = (clone $query)->where('payment_method', 'debt')->sum('debt_amount');
        $averageSaleValue = $totalSalesCount > 0 ? $totalRevenue / $totalSalesCount : 0;
        $voidedSalesCount = Sale::where('is_voided', true)
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('sale_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('sale_date', '<=', $request->date_to))
            ->count();

        $users = \App\Models\User::all();
        $customers = Customer::all();

        return view('reports.sales.summary', compact(
            'sales', 'totalSalesCount', 'totalRevenue', 'totalCashCollected',
            'totalDebtCreated', 'averageSaleValue', 'voidedSalesCount', 'users', 'customers'
        ));
    }

    public function salesByPeriod(Request $request)
    {
        $groupBy = $request->input('group_by', 'day');
        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };

        $salesByPeriod = Sale::selectRaw("DATE_FORMAT(sale_date, '{$dateFormat}') as period, COUNT(*) as count, SUM(total_amount) as total")
            ->where('is_voided', false)
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('sale_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('sale_date', '<=', $request->date_to))
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get();

        return view('reports.sales.by-period', compact('salesByPeriod', 'groupBy'));
    }

    public function profit(Request $request)
    {
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.is_voided', false);

        if ($request->filled('date_from')) $query->whereDate('sales.sale_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('sales.sale_date', '<=', $request->date_to);
        if ($request->filled('category_id')) $query->where('products.category_id', $request->category_id);

        $profitData = $query->selectRaw('
            products.id, products.name, products.category_id,
            SUM(sale_items.quantity) as total_quantity,
            SUM(sale_items.total_price) as total_revenue,
            SUM(sale_items.quantity * products.cost_price) as total_cost
        ')
        ->groupBy('products.id', 'products.name', 'products.category_id')
        ->get()
        ->map(function($item) {
            $item->profit = $item->total_revenue - $item->total_cost;
            $item->margin = $item->total_revenue > 0 ? ($item->profit / $item->total_revenue) * 100 : 0;
            return $item;
        });

        $totalRevenue = $profitData->sum('total_revenue');
        $totalCost = $profitData->sum('total_cost');
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $categories = \App\Models\Category::all();

        return view('reports.profit.summary', compact(
            'profitData', 'totalRevenue', 'totalCost', 'grossProfit', 'profitMargin', 'categories'
        ));
    }

    public function lowStock()
    {
        $threshold = Setting::get('low_stock_threshold', 10);
        $products = Product::with(['category', 'supplier'])
            ->where('quantity', '<', $threshold)
            ->where('is_active', true)
            ->orderBy('quantity', 'asc')
            ->get();

        return view('reports.inventory.low-stock', compact('products', 'threshold'));
    }

    public function stockValue()
    {
        $products = Product::where('is_active', true)->with('category')->get();
        $totalStockValue = $products->sum(fn($p) => $p->quantity * $p->cost_price);
        $valueByCategory = $products->groupBy('category.name')->map(fn($items) =>
            $items->sum(fn($p) => $p->quantity * $p->cost_price)
        );
        $valuableProducts = $products->sortByDesc(fn($p) => $p->quantity * $p->cost_price)->take(10);

        return view('reports.inventory.stock-value', compact('totalStockValue', 'valueByCategory', 'valuableProducts'));
    }

    public function customerDebt()
    {
        $customers = Customer::get()
            ->filter(fn($c) => $c->total_debt > 0)
            ->map(function($c) {
                $c->total_purchases = $c->sales()->where('is_voided', false)->sum('total_amount');
                $c->total_paid = abs($c->debtTransactions()->where('transaction_type', 'payment')->whereNull('voided_at')->sum('amount'));
                $c->last_payment_date = $c->debtTransactions()->where('transaction_type', 'payment')->latest()->first()?->created_at;
                return $c;
            })
            ->sortByDesc('total_debt');

        $totalDebt = $customers->sum('total_debt');

        return view('reports.customers.debt-summary', compact('customers', 'totalDebt'));
    }

    public function customerDebtAging()
    {
        $agingData = Customer::get()
            ->filter(fn($customer) => $customer->total_debt > 0)
            ->map(function($customer) {
            $sales = Sale::where('customer_id', $customer->id)
                ->where('is_voided', false)
                ->where('debt_amount', '>', 0)
                ->get();

            $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];

            foreach ($sales as $sale) {
                $days = now()->diffInDays($sale->sale_date);
                if ($days <= 30) $aging['0-30'] += $sale->debt_amount;
                elseif ($days <= 60) $aging['31-60'] += $sale->debt_amount;
                elseif ($days <= 90) $aging['61-90'] += $sale->debt_amount;
                else $aging['90+'] += $sale->debt_amount;
            }

            return [
                'customer' => $customer,
                'aging' => $aging,
                'total' => array_sum($aging),
            ];
        })->filter(fn($item) => $item['total'] > 0);

        return view('reports.customers.debt-aging', compact('agingData'));
    }

    public function supplierDebt()
    {
        $suppliers = Supplier::get()
            ->filter(fn($s) => $s->total_debt > 0)
            ->map(function($s) {
                $s->total_purchases = $s->purchases()->where('is_voided', false)->sum('total_amount');
                $s->total_paid = $s->purchases()->where('is_voided', false)->sum('paid_amount');
                return $s;
            })
            ->sortByDesc('total_debt');

        $totalDebt = $suppliers->sum('total_debt');

        return view('reports.suppliers.debt-summary', compact('suppliers', 'totalDebt'));
    }

    public function supplierDebtAging()
    {
        $agingData = Supplier::get()
            ->filter(fn($supplier) => $supplier->total_debt > 0)
            ->map(function($supplier) {
            $purchases = Purchase::where('supplier_id', $supplier->id)
                ->where('is_voided', false)
                ->where('debt_amount', '>', 0)
                ->get();

            $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];

            foreach ($purchases as $purchase) {
                $days = now()->diffInDays($purchase->purchase_date);
                if ($days <= 30) $aging['0-30'] += $purchase->debt_amount;
                elseif ($days <= 60) $aging['31-60'] += $purchase->debt_amount;
                elseif ($days <= 90) $aging['61-90'] += $purchase->debt_amount;
                else $aging['90+'] += $purchase->debt_amount;
            }

            return [
                'supplier' => $supplier,
                'aging' => $aging,
                'total' => array_sum($aging),
            ];
        })->filter(fn($item) => $item['total'] > 0);

        return view('reports.suppliers.debt-aging', compact('agingData'));
    }

    public function returns(Request $request)
    {
        $query = ReturnModel::with(['sale', 'user', 'returnItems.product'])->notVoided();

        if ($request->filled('date_from')) $query->whereDate('return_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('return_date', '<=', $request->date_to);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);

        $returns = $query->latest('return_date')->paginate(20);
        $totalReturns = (clone $query)->count();
        $totalReturnAmount = (clone $query)->sum('total_return_amount');
        $totalCashRefund = (clone $query)->sum('cash_refund_amount');
        $totalDebtReduction = (clone $query)->sum('debt_reduction_amount');

        $topReturnedProducts = DB::table('return_items')
            ->join('returns', 'return_items.return_id', '=', 'returns.id')
            ->join('products', 'return_items.product_id', '=', 'products.id')
            ->where('returns.is_voided', false)
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('returns.return_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('returns.return_date', '<=', $request->date_to))
            ->selectRaw('products.name, SUM(return_items.quantity) as total_quantity, COUNT(DISTINCT return_items.return_id) as return_count')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return view('reports.returns.summary', compact(
            'returns', 'totalReturns', 'totalReturnAmount',
            'totalCashRefund', 'totalDebtReduction', 'topReturnedProducts'
        ));
    }

    // Export Methods
    public function exportSales(Request $request)
    {
        return Excel::download(new SalesExport($request->all()), 'sales-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportSalesPdf(Request $request)
    {
        $query = Sale::with(['customer', 'user'])->notVoided();

        if ($request->filled('date_from')) $query->whereDate('sale_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('sale_date', '<=', $request->date_to);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);

        $sales = $query->latest('sale_date')->get();
        $totalRevenue = $sales->sum('total_amount');
        $storeName = Setting::get('store_name', 'المتجر');

        $pdf = PDF::loadView('reports.sales.pdf', compact('sales', 'totalRevenue', 'storeName'));
        return $pdf->download('sales-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportProfit(Request $request)
    {
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.is_voided', false);

        if ($request->filled('date_from')) $query->whereDate('sales.sale_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('sales.sale_date', '<=', $request->date_to);
        if ($request->filled('category_id')) $query->where('products.category_id', $request->category_id);

        $profitData = $query->selectRaw('
            products.id, products.name, products.category_id,
            SUM(sale_items.quantity) as total_quantity,
            SUM(sale_items.total_price) as total_revenue,
            SUM(sale_items.quantity * products.cost_price) as total_cost
        ')
        ->groupBy('products.id', 'products.name', 'products.category_id')
        ->get()
        ->map(function($item) {
            $item->profit = $item->total_revenue - $item->total_cost;
            $item->margin = $item->total_revenue > 0 ? ($item->profit / $item->total_revenue) * 100 : 0;
            return $item;
        });

        $exportData = $profitData->map(function($item) {
            return [
                'name' => $item->name,
                'total_quantity' => $item->total_quantity,
                'total_revenue' => $item->total_revenue,
                'total_cost' => $item->total_cost,
                'profit' => $item->profit,
                'margin' => $item->margin,
            ];
        })->toArray();

        return Excel::download(new ProfitExport($exportData), 'profit-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportLowStock()
    {
        $threshold = Setting::get('low_stock_threshold', 10);
        return Excel::download(new LowStockExport($threshold), 'low-stock-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportCustomerDebtAging()
    {
        $agingData = Customer::get()
            ->filter(fn($customer) => $customer->total_debt > 0)
            ->map(function($customer) {
            $sales = Sale::where('customer_id', $customer->id)
                ->where('is_voided', false)
                ->where('debt_amount', '>', 0)
                ->get();

            $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];

            foreach ($sales as $sale) {
                $days = now()->diffInDays($sale->sale_date);
                if ($days <= 30) $aging['0-30'] += $sale->debt_amount;
                elseif ($days <= 60) $aging['31-60'] += $sale->debt_amount;
                elseif ($days <= 90) $aging['61-90'] += $sale->debt_amount;
                else $aging['90+'] += $sale->debt_amount;
            }

            return [
                'customer' => $customer,
                'aging' => $aging,
                'total' => array_sum($aging),
            ];
        })->filter(fn($item) => $item['total'] > 0)->values();

        return Excel::download(new DebtAgingExport($agingData, 'customer'), 'customer-debt-aging-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportSupplierDebtAging()
    {
        $agingData = Supplier::get()
            ->filter(fn($supplier) => $supplier->total_debt > 0)
            ->map(function($supplier) {
            $purchases = Purchase::where('supplier_id', $supplier->id)
                ->where('is_voided', false)
                ->where('debt_amount', '>', 0)
                ->get();

            $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];

            foreach ($purchases as $purchase) {
                $days = now()->diffInDays($purchase->purchase_date);
                if ($days <= 30) $aging['0-30'] += $purchase->debt_amount;
                elseif ($days <= 60) $aging['31-60'] += $purchase->debt_amount;
                elseif ($days <= 90) $aging['61-90'] += $purchase->debt_amount;
                else $aging['90+'] += $purchase->debt_amount;
            }

            return [
                'supplier' => $supplier,
                'aging' => $aging,
                'total' => array_sum($aging),
            ];
        })->filter(fn($item) => $item['total'] > 0)->values();

        return Excel::download(new DebtAgingExport($agingData, 'supplier'), 'supplier-debt-aging-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportReturns(Request $request)
    {
        return Excel::download(new ReturnsExport($request->all()), 'returns-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportReturnsPdf(Request $request)
    {
        $query = ReturnModel::with(['sale.customer', 'returnItems.product'])->notVoided();

        if ($request->filled('date_from')) $query->whereDate('return_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('return_date', '<=', $request->date_to);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);

        $returns = $query->latest('return_date')->get();
        $totalReturnAmount = $returns->sum('total_return_amount');
        $totalCashRefund = $returns->sum('cash_refund_amount');
        $totalDebtReduction = $returns->sum('debt_reduction_amount');
        $storeName = Setting::get('store_name', 'المتجر');

        $pdf = PDF::loadView('reports.returns.pdf', compact(
            'returns', 'totalReturnAmount', 'totalCashRefund', 'totalDebtReduction', 'storeName'
        ));
        return $pdf->download('returns-report-' . now()->format('Y-m-d') . '.pdf');
    }
}

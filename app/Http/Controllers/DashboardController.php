<?php

namespace App\Http\Controllers;

use App\Models\{Sale, Purchase, ReturnModel, Customer, Supplier, Product, ActivityLog, Setting};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's metrics
        $todaySales = Sale::today()->notVoided()->count();
        $todaySalesAmount = Sale::today()->notVoided()->sum('total_amount');
        $todayPurchases = Purchase::today()->notVoided()->count();
        $todayPurchasesAmount = Purchase::today()->notVoided()->sum('total_amount');
        $todayReturns = ReturnModel::today()->notVoided()->count();
        $todayReturnsAmount = ReturnModel::today()->notVoided()->sum('total_return_amount');

        // This month
        $thisMonthSales = Sale::thisMonth()->notVoided()->count();
        $thisMonthSalesAmount = Sale::thisMonth()->notVoided()->sum('total_amount');

        // Debt
        $customerDebt = Customer::get()->sum(function($customer) {
            return $customer->total_debt;
        });
        $supplierDebt = Supplier::get()->sum(function($supplier) {
            return $supplier->total_debt;
        });

        // Low stock
        $lowStockThreshold = Setting::get('low_stock_threshold', 10);
        $lowStockCount = Product::where('quantity', '<', $lowStockThreshold)->where('is_active', true)->count();
        $lowStockProducts = Product::where('quantity', '<', $lowStockThreshold)
            ->where('is_active', true)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        // Sales trend (last 7 days)
        $salesTrend = Sale::selectRaw('DATE(sale_date) as date, COUNT(*) as count, SUM(total_amount) as total')
            ->where('sale_date', '>=', now()->subDays(7))
            ->where('is_voided', false)
            ->groupBy('date')
            ->orderBy('sale_date', 'asc')
            ->get();

        // Top 5 products this month
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.sale_date', '>=', now()->startOfMonth())
            ->where('sales.is_voided', false)
            ->selectRaw('products.id, products.name, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.total_price) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        $recentActivity = ActivityLog::getRecent(10);

        // Cash flow today
        $cashIn = Sale::today()->notVoided()->where('payment_method', 'cash')->sum('total_amount');
        $cashOut = Purchase::today()->notVoided()->where('paid_amount', '>', 0)->sum('paid_amount');
        $cashFlow = $cashIn - $cashOut;

        return view('dashboard.index', compact(
            'todaySales', 'todaySalesAmount', 'todayPurchases', 'todayPurchasesAmount',
            'todayReturns', 'todayReturnsAmount', 'thisMonthSales', 'thisMonthSalesAmount',
            'customerDebt', 'supplierDebt', 'lowStockCount', 'lowStockProducts',
            'salesTrend', 'topProducts', 'recentActivity', 'cashIn', 'cashOut', 'cashFlow'
        ));
    }
}

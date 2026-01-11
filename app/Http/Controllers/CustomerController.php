<?php


namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');

        $query = Customer::query();

        if ($search) {
            $query->search($search);
        }

        switch ($filter) {
            case 'active':
                $query->active();
                break;
            case 'inactive':
                $query->where('is_active', false);
                break;
            case 'with_debt':
                $query->withDebt();
                break;
        }

        $customers = $query->orderBy('name')->paginate($request->get('per_page', 10));

        // Calculate statistics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $customersWithDebt = Customer::withDebt()->count();
        $totalCreditLimit = Customer::sum('credit_limit');

        // Calculate total debt from transactions
        $totalDebt = \App\Models\CustomerDebtTransaction::whereNull('voided_at')
            ->sum('amount') ?? 0;

        return view('customers.index', compact(
            'customers',
            'search',
            'filter',
            'totalCustomers',
            'activeCustomers',
            'totalDebt',
            'customersWithDebt',
            'totalCreditLimit'
        ));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'required|numeric|min:0',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'credit_limit' => $request->credit_limit,
            'is_active' => true,
        ]);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'تم إضافة العميل بنجاح');
    }

    public function show(Request $request, Customer $customer)
    {
        $salesWithDebt = $customer->getSalesWithDebt();
        $fullyPaidSales = $customer->getFullyPaidDebtSales();

        // Filter transactions by sale if specified
        $saleFilter = $request->query('sale_filter');
        $transactionsQuery = $customer->debtTransactions()->with('sale');

        if ($saleFilter) {
            $transactionsQuery->where('sale_id', $saleFilter);
        }

        $transactions = $transactionsQuery->orderBy('created_at', 'desc')->get();

        // Calculate running balance from oldest to newest
        $runningBalance = 0;
        $chronological = $transactions->reverse(); // Reverse to get oldest first
        foreach ($chronological as $transaction) {
            if (!$transaction->isVoided()) {
                $runningBalance += $transaction->amount;
            }
            $transaction->running_balance = $runningBalance;
        }

        $currentDebt = $customer->total_debt;
        $creditUtilization = $customer->getCreditUtilizationPercentage();

        return view('customers.show', compact(
            'customer',
            'salesWithDebt',
            'fullyPaidSales',
            'transactions',
            'currentDebt',
            'creditUtilization',
            'saleFilter'
        ));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent setting credit limit below current debt
        if ($request->credit_limit < $customer->total_debt) {
            return redirect()->back()
                ->with('error', "لا يمكن تعيين حد الائتمان أقل من المديونية الحالية ($" . number_format($customer->total_debt, 2) . ")")
                ->withInput();
        }

        // Prevent inactivating a customer with debt
        if ($request->has('is_active') && !$request->is_active && $customer->total_debt > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن إلغاء تفعيل عميل لديه مديونية نشطة')
                ->withInput();
        }

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'credit_limit' => $request->credit_limit,
            'is_active' => $request->has('is_active') ? $request->is_active : $customer->is_active,
        ]);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    public function destroy(Customer $customer)
    {
        // Prevent deleting a customer with debt
        if ($customer->total_debt > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف عميل لديه مديونية نشطة ($' . number_format($customer->total_debt, 2) . ')');
        }

        // Prevent deleting a customer with sales
        if ($customer->sales()->count() > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف عميل لديه مبيعات مسجلة');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'تم حذف العميل بنجاح');
    }

    public function toggleStatus(Customer $customer)
    {
        // Prevent inactivating a customer with debt
        if (!$customer->is_active && $customer->total_debt > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن إلغاء تفعيل عميل لديه مديونية نشطة ($' . number_format($customer->total_debt, 2) . ')');
        }

        $customer->is_active = !$customer->is_active;
        $customer->save();

        $status = $customer->is_active ? 'تفعيل' : 'إلغاء تفعيل';

        return redirect()->back()
            ->with('success', "تم {$status} العميل بنجاح");
    }
}

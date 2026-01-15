<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display listing of suppliers with search and filter
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter'); // 'all', 'active', 'inactive', 'with_debt'

        $query = Supplier::query();

        // Search by name, contact person, or phone
        if ($search) {
            $query->search($search);
        }

        // Filter by status or debt
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

        $suppliers = $query->orderBy('name')->paginate(20);

        // Calculate statistics
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('is_active', true)->count();
        $suppliersWithDebt = Supplier::withDebt()->count();
        $totalDebt = Supplier::where('is_active', true)->sum('total_debt');

        return view('suppliers.index', compact(
            'suppliers',
            'search',
            'filter',
            'totalSuppliers',
            'activeSuppliers',
            'suppliersWithDebt',
            'totalDebt'
        ));
    }

    /**
     * Show form for creating new supplier
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store new supplier
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $supplier = Supplier::create([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'address' => $request->address,
            'total_debt' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'تم إضافة المورد بنجاح');
    }

    /**
     * Display supplier details with purchases and transactions
     */
    public function show(Supplier $supplier)
    {
        // Get purchases with outstanding debt
        $purchasesWithDebt = $supplier->getPurchasesWithDebt();

        // Get paid purchases (fully paid)
        $paidPurchases = $supplier->purchases()
            ->where('is_voided', false)
            ->where('debt_amount', '=', 0)
            ->with('debtTransactions')
            ->latest('purchase_date')
            ->get();

        // Get transaction history
        $transactions = $supplier->getTransactionHistory();

        // Calculate running balance
        $runningBalance = 0;
        foreach ($transactions as $transaction) {
            if (!$transaction->isVoided()) {
                $runningBalance += $transaction->amount;
            }
            $transaction->running_balance = $runningBalance;
        }

        $currentDebt = $supplier->total_debt;

        return view('suppliers.show', compact(
            'supplier',
            'purchasesWithDebt',
            'paidPurchases',
            'transactions',
            'currentDebt'
        ));
    }

    /**
     * Show form for editing supplier
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $supplier->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'تم تحديث بيانات المورد بنجاح');
    }

    /**
     * Delete supplier (with validation)
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has debt
        if ($supplier->total_debt != 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف مورد لديه مديونية نشطة');
        }

        // Check if supplier has purchases
        if ($supplier->purchases()->count() > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف مورد لديه مشتريات مسجلة');
        }

        // Check if supplier has products
        if ($supplier->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف مورد لديه منتجات مرتبطة');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'تم حذف المورد بنجاح');
    }

    /**
     * Toggle supplier active status
     */
    public function toggleStatus(Supplier $supplier)
    {
        $supplier->is_active = !$supplier->is_active;
        $supplier->save();

        $status = $supplier->is_active ? 'تفعيل' : 'إلغاء تفعيل';

        return redirect()->back()
            ->with('success', "تم {$status} المورد بنجاح");
    }
}

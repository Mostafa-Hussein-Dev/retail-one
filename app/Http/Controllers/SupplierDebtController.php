<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Purchase;
use Illuminate\Http\Request;

class SupplierDebtController extends Controller
{
    /**
     * Show payment form for a specific purchase
     */
    public function showPaymentForm(Supplier $supplier, Purchase $purchase)
    {
        // Validate that purchase belongs to supplier
        if ($purchase->supplier_id !== $supplier->id) {
            return redirect()->back()
                ->with('error', 'هذا الشراء لا يخص هذا المورد');
        }

        // Check if purchase is voided
        if ($purchase->is_voided) {
            return redirect()->back()
                ->with('error', 'لا يمكن الدفع على شراء ملغي');
        }

        // Check if purchase has remaining debt
        if ($purchase->debt_amount <= 0) {
            return redirect()->back()
                ->with('info', 'هذا الشراء مدفوع بالكامل');
        }

        return view('supplier-debt.payment-form', compact('supplier', 'purchase'));
    }

    /**
     * Record payment to supplier
     */
    public function recordPayment(Request $request, Supplier $supplier, Purchase $purchase)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $purchase->debt_amount,
        ]);

        // Validate purchase belongs to supplier
        if ($purchase->supplier_id !== $supplier->id || $purchase->is_voided) {
            return redirect()->back()->with('error', 'عملية غير صالحة');
        }

        // Use existing Purchase::processPayment() method
        if ($purchase->processPayment($request->amount)) {
            return redirect()->route('suppliers.show', $supplier)
                ->with('success', 'تم تسجيل الدفعة بنجاح');
        }

        return redirect()->back()
            ->with('error', 'حدث خطأ أثناء تسجيل الدفعة')
            ->withInput();
    }
}

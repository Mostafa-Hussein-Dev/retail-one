<?php


namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;

class CustomerDebtController extends Controller
{
    public function showPaymentForm(Customer $customer, Sale $sale)
    {
        if ($sale->customer_id !== $customer->id) {
            return redirect()->route('customers.index')
                ->with('error', 'هذا البيع لا يخص هذا العميل');
        }

        if ($sale->is_voided) {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'لا يمكن الدفع على بيع ملغي');
        }

        if ($sale->debt_amount <= 0) {
            return redirect()->route('customers.show', $customer)
                ->with('warning', 'هذا البيع مدفوع بالكامل');
        }

        return view('debt.payment-form', compact('customer', 'sale'));
    }

    public function recordPayment(Request $request, Customer $customer, Sale $sale)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->debt_amount,
        ]);

        if ($sale->customer_id !== $customer->id || $sale->is_voided) {
            return redirect()->back()->with('error', 'عملية غير صالحة');
        }

        // Use existing Sale::processPayment() method
        if ($sale->processPayment($request->amount)) {
            return redirect()->route('sales.show', $sale)
                ->with('success', 'تم تسجيل الدفعة بنجاح');
        }

        return redirect()->back()
            ->with('error', 'حدث خطأ أثناء تسجيل الدفعة')
            ->withInput();
    }
}

<?php

namespace App\Http\Controllers\ParentPortal;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class FeesController extends ParentBaseController
{
    public function index(Request $request)
    {
        $this->markOverdue();
        $child = $this->child($request);
        $invoices = Invoice::with('payments')->where('student_id', $child->id)->orderByRaw("case status when 'overdue' then 0 when 'pending' then 1 else 2 end")->orderByDesc('due_on')->get();
        $payments = Payment::with('invoice')->whereIn('invoice_id', $invoices->pluck('id'))->latest('paid_on')->latest('id')->get();

        return view('parent.fees', ['child' => $child, 'children' => $this->children(), 'invoices' => $invoices, 'payments' => $payments]);
    }

    public function invoice(Invoice $invoice)
    {
        $this->ownsStudent($invoice->student_id);

        return view('parent.invoice', ['invoice' => $invoice->load(['student', 'payments'])]);
    }

    public function pay(Invoice $invoice)
    {
        $this->ownsStudent($invoice->student_id);

        return view('parent.pay', ['invoice' => $invoice->load(['student', 'payments']), 'children' => $this->children(), 'child' => $invoice->student]);
    }

    public function receipt(Payment $payment)
    {
        $payment->load('invoice.student');
        $this->ownsStudent($payment->invoice->student_id);

        return view('parent.receipt', ['payment' => $payment]);
    }
}

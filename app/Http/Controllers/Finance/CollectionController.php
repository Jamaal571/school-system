<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CollectionController extends Controller
{
    private const METHODS = 'cash,bank transfer,cheque,mobile money';

    public function index(Request $request)
    {
        Invoice::where('status', 'pending')->whereDate('due_on', '<', today())->update(['status' => 'overdue']);

        $invoices = Invoice::with(['student.enrollments.schoolClass', 'student.guardians', 'payments'])->where('status', '!=', 'paid')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('number', 'like', "%$t%")
                ->orWhereHas('student', fn ($s) => $s->where('first_name', 'like', "%$t%")->orWhere('last_name', 'like', "%$t%")->orWhere('admission_no', 'like', "%$t%"))))
            ->orderByRaw("case status when 'overdue' then 0 else 1 end")->orderBy('due_on')
            ->paginate(15)->withQueryString();

        return view('finance.collections', ['invoices' => $invoices, 'methods' => explode(',', self::METHODS)]);
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $balance = $invoice->load('payments')->balance;
        $data = $request->validate([
            'amount' => "required|numeric|min:0.01|max:$balance",
            'method' => 'required|in:'.self::METHODS,
        ], ['amount.max' => "The most you can record for this invoice is $balance."]);

        return back()->with('status', $this->record($invoice, (float) $data['amount'], $data['method']));
    }

    public function offline(Request $request)
    {
        $data = $request->validate([
            'invoice_number' => 'required|exists:invoices,number',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:'.self::METHODS,
        ], ['invoice_number.exists' => 'No invoice has that number.']);

        $invoice = Invoice::with('payments')->where('number', $data['invoice_number'])->first();
        abort_if($data['amount'] > $invoice->balance, 422, "The most you can record for {$invoice->number} is {$invoice->balance}.");

        return redirect()->route('finance.collections')->with('status', $this->record($invoice, (float) $data['amount'], $data['method']));
    }

    public function remind(Invoice $invoice)
    {
        $invoice->load(['student.guardians', 'payments']);
        $email = $invoice->student->guardians->first()?->email;
        if (! $email) {
            return back()->with('warning', "No guardian email on file for {$invoice->student->full_name}.");
        }

        try {
            Mail::raw("Dear parent or guardian,\n\nA balance of $".number_format($invoice->balance, 2)." for {$invoice->student->full_name} ({$invoice->number}) was due on {$invoice->due_on->format('j F Y')}. Please contact the school finance office to arrange payment.\n\nThank you,\nNorthwood Academy",
                fn ($m) => $m->to($email)->subject("Fee reminder: {$invoice->number}"));
        } catch (\Throwable $e) {
            return back()->with('warning', 'The reminder could not be sent: '.$e->getMessage());
        }
        $invoice->update(['last_reminded_at' => now()]);

        return back()->with('status', "Reminder sent to $email.");
    }

    private function record(Invoice $invoice, float $amount, string $method): string
    {
        Payment::create(['invoice_id' => $invoice->id, 'amount' => $amount, 'paid_on' => now()->toDateString(), 'method' => $method, 'received_by' => auth()->id()]);
        if ((float) $invoice->payments()->sum('amount') >= (float) $invoice->amount) {
            $invoice->update(['status' => 'paid']);
        }

        return 'Payment of $'.number_format($amount, 2)." recorded for {$invoice->number}.";
    }
}

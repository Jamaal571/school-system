<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        // Unpaid invoices past their due date become overdue.
        Invoice::where('status', 'pending')->whereDate('due_on', '<', today())->update(['status' => 'overdue']);

        $invoices = Invoice::with(['student', 'payments'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $t) => $q->whereHas('student', fn ($s) => $s->where(fn ($w) => $w
                ->where('first_name', 'like', "%$t%")
                ->orWhere('last_name', 'like', "%$t%")
                ->orWhere('admission_no', 'like', "%$t%"))))
            ->orderByRaw("case status when 'overdue' then 0 when 'pending' then 1 else 2 end")
            ->orderBy('due_on')
            ->paginate(15)->withQueryString();

        $invoiced = (float) Invoice::sum('amount');
        $collected = (float) Payment::sum('amount');
        $overdue = Invoice::where('status', 'overdue');

        return view('admin.finance.index', [
            'invoices' => $invoices,
            'invoiced' => $invoiced,
            'collected' => $collected,
            'outstanding' => max($invoiced - $collected, 0),
            'overdueCount' => (clone $overdue)->count(),
            'overdueTotal' => (float) (clone $overdue)->sum('amount'),
        ]);
    }

    public function storeInvoice(Request $request)
    {
        $data = $request->validate([
            'admission_no' => 'required|exists:students,admission_no',
            'description' => 'nullable|string|max:120',
            'amount' => 'required|numeric|min:1|max:1000000',
            'due_on' => 'required|date',
        ], ['admission_no.exists' => 'No student has that admission number.']);

        $year = AcademicYear::current();
        abort_if(! $year, 422, 'Create an academic year first.');

        $invoice = Invoice::create([
            'student_id' => Student::where('admission_no', $data['admission_no'])->value('id'),
            'academic_year_id' => $year->id,
            'number' => 'INV-'.str_pad(Invoice::max('id') + 1, 5, '0', STR_PAD_LEFT),
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'due_on' => $data['due_on'],
            'status' => 'pending',
        ]);

        return redirect()->route('admin.finance')->with('status', "Invoice $invoice->number created.");
    }

    public function storePayment(Request $request, Invoice $invoice)
    {
        $balance = round((float) $invoice->amount - (float) $invoice->payments()->sum('amount'), 2);

        $data = $request->validate([
            'amount' => "required|numeric|min:0.01|max:$balance",
            'method' => 'required|in:cash,bank transfer,mobile money',
        ], ['amount.max' => "The most you can record for this invoice is $balance."]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $data['amount'],
            'paid_on' => now()->toDateString(),
            'method' => $data['method'],
            'received_by' => auth()->id(),
        ]);

        if ((float) $invoice->payments()->sum('amount') >= (float) $invoice->amount) {
            $invoice->update(['status' => 'paid']);
        }

        return redirect()->back()->with('status', 'Payment of $'.number_format((float) $data['amount'], 2)." recorded for $invoice->number.");
    }
}

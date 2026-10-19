<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with(['student', 'payments'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('number', 'like', "%$t%")
                ->orWhereHas('student', fn ($s) => $s->where('first_name', 'like', "%$t%")->orWhere('last_name', 'like', "%$t%")->orWhere('admission_no', 'like', "%$t%"))))
            ->latest('id')->paginate(15)->withQueryString();

        return view('finance.invoices', compact('invoices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'admission_no' => 'required|exists:students,admission_no',
            'category' => 'required|in:tuition,transport,activities',
            'description' => 'nullable|string|max:120',
            'amount' => 'required|numeric|min:1|max:1000000',
            'due_on' => 'required|date',
        ], ['admission_no.exists' => 'No student has that admission number.']);

        $year = AcademicYear::current();
        abort_if(! $year, 422, 'The admin must set up the academic year first.');

        $invoice = Invoice::create([
            'student_id' => Student::where('admission_no', $data['admission_no'])->value('id'),
            'academic_year_id' => $year->id,
            'number' => 'INV-'.str_pad(Invoice::max('id') + 1, 5, '0', STR_PAD_LEFT),
            'category' => $data['category'], 'description' => $data['description'] ?? null,
            'amount' => $data['amount'], 'due_on' => $data['due_on'], 'status' => 'pending',
        ]);

        return redirect()->route('finance.invoices')->with('status', "Invoice {$invoice->number} created.");
    }
}

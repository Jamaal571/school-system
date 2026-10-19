<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('finance.reports');
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['statement', 'fees', 'transactions'], true), 404);
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'status' => 'nullable|in:paid,pending,overdue']);

        return response()->streamDownload(function () use ($type, $request) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $this->{$type}($out, $request);
            fclose($out);
        }, "financial-$type-".now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function put($out, array $row): void
    {
        fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'".$v : $v, $row));
    }

    private function statement($out, Request $request): void
    {
        $this->put($out, ['Month', 'Revenue', 'Expenses', 'Net']);
        foreach (range(11, 0) as $ago) {
            $m = now()->subMonths($ago)->startOfMonth();
            $end = $m->copy()->endOfMonth();
            $revenue = (float) Payment::whereDate('paid_on', '>=', $m->toDateString())->whereDate('paid_on', '<=', $end->toDateString())->sum('amount');
            $expenses = (float) Expense::between($m, $end)->sum('amount');
            $this->put($out, [$m->format('Y-m'), number_format($revenue, 2, '.', ''), number_format($expenses, 2, '.', ''), number_format($revenue - $expenses, 2, '.', '')]);
        }
    }

    private function fees($out, Request $request): void
    {
        $this->put($out, ['Invoice', 'Admission no', 'Student', 'Category', 'Description', 'Amount', 'Paid', 'Balance', 'Due', 'Status']);
        Invoice::with(['student', 'payments'])->when($request->status, fn ($q, $s) => $q->where('status', $s))->orderBy('id')->chunk(200, function ($invoices) use ($out) {
            foreach ($invoices as $i) {
                $this->put($out, [$i->number, $i->student?->admission_no, $i->student?->full_name, $i->category, $i->description,
                    number_format((float) $i->amount, 2, '.', ''), number_format((float) $i->payments->sum('amount'), 2, '.', ''),
                    number_format($i->balance, 2, '.', ''), $i->due_on->toDateString(), $i->status]);
            }
        });
    }

    private function transactions($out, Request $request): void
    {
        $from = $request->from ? Carbon::parse($request->from) : now()->subDays(30);
        $to = $request->to ? Carbon::parse($request->to) : now();

        $this->put($out, ['Date', 'Type', 'Reference', 'Description', 'Category', 'Amount']);
        $rows = Payment::with('invoice.student')->whereDate('paid_on', '>=', $from->toDateString())->whereDate('paid_on', '<=', $to->toDateString())->get()
            ->map(fn ($p) => [Carbon::parse($p->paid_on)->toDateString(), 'income', $p->invoice->number, $p->invoice->student->full_name, $p->invoice->category, number_format((float) $p->amount, 2, '.', '')])
            ->concat(Expense::between($from, $to)->get()->map(fn ($e) => [$e->spent_on->toDateString(), 'expense', $e->reference, $e->description, $e->category, number_format(-(float) $e->amount, 2, '.', '')]))
            ->sortBy(0);
        foreach ($rows as $r) {
            $this->put($out, $r);
        }
    }
}

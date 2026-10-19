<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StaffMember;
use Carbon\Carbon;

class OverviewController extends Controller
{
    public function index()
    {
        // Unpaid invoices past their due date become overdue.
        Invoice::where('status', 'pending')->whereDate('due_on', '<', today())->update(['status' => 'overdue']);

        $now = now();
        $start = $now->copy()->startOfMonth();
        $paid = fn (Carbon $a, Carbon $b) => (float) Payment::whereDate('paid_on', '>=', $a->toDateString())->whereDate('paid_on', '<=', $b->toDateString())->sum('amount');
        $spent = fn (Carbon $a, Carbon $b, $cat = null) => (float) Expense::between($a, $b)->when($cat, fn ($q, $c) => $q->where('category', $c))->sum('amount');

        // ---- Stat cards ----
        $collected = $paid($start, $now);
        $prevStart = $start->copy()->subMonth();
        $prevEnd = $prevStart->copy()->addDays($now->day - 1)->min($prevStart->copy()->endOfMonth());
        $prev = $paid($prevStart, $prevEnd);
        $change = $prev > 0 ? round(($collected - $prev) / $prev * 100, 1) : null;

        $unpaid = Invoice::where('status', '!=', 'paid')->withSum('payments', 'amount')->get();
        $outstanding = $unpaid->sum(fn ($i) => max((float) $i->amount - (float) $i->payments_sum_amount, 0));
        $overdueCount = $unpaid->where('status', 'overdue')->count();

        $payroll = $spent($start, $now, 'salaries');
        $expenses = $spent($start, $now);
        $staff = StaffMember::where('status', 'active')->count();
        $net = $collected - $expenses;

        $stats = [
            ['label' => 'Collected this month', 'value' => '$'.number_format($collected), 'icon' => 'dollar',
             'note' => $change === null ? 'Month to date' : abs($change).'% vs. same period in '.$prevStart->format('F'),
             'tone' => $change === null ? 'muted' : ($change >= 0 ? 'success' : 'danger'), 'arrow' => $change === null ? false : ($change >= 0 ? true : 'down')],
            ['label' => 'Pending / overdue fees', 'value' => '$'.number_format($outstanding), 'icon' => 'clock',
             'note' => "$overdueCount student ".\Illuminate\Support\Str::plural('balance', $overdueCount).' need attention', 'tone' => 'warning'],
            ['label' => 'Payroll expenses', 'value' => '$'.number_format($payroll), 'icon' => 'users',
             'note' => $payroll > 0 ? "$staff staff · {$now->format('F')} payroll posted" : "$staff staff · {$now->format('F')} payroll not processed yet", 'tone' => 'muted'],
            ['label' => 'Net balance', 'value' => ($net < 0 ? '−' : '').'$'.number_format(abs($net)), 'icon' => 'wallet',
             'note' => '$'.number_format($collected).' revenue − $'.number_format($expenses).' expenses', 'tone' => $net < 0 ? 'danger' : 'primary'],
        ];

        // ---- Revenue vs expenses, last three months ----
        $months = collect([2, 1, 0])->map(function ($ago) use ($now, $paid, $spent) {
            $m = $now->copy()->subMonths($ago)->startOfMonth();
            $end = $ago === 0 ? $now : $m->copy()->endOfMonth();

            return ['label' => $m->format('F').($ago === 0 ? ' · MTD' : ''), 'a' => $paid($m, $end), 'b' => $spent($m, $end)];
        });
        $max = max($months->max('a'), $months->max('b'), 1);
        $step = 10 ** floor(log10($max));
        $top = ceil($max / $step) * $step;

        $revenueBy = Payment::join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->whereDate('paid_on', '>=', $start->toDateString())->whereDate('paid_on', '<=', $now->toDateString())
            ->selectRaw('invoices.category as category, sum(payments.amount) as total')->groupBy('invoices.category')->pluck('total', 'category');
        $expenseBy = Expense::between($start, $now)->selectRaw('category, sum(amount) as total')->groupBy('category')->pluck('total', 'category');
        $otherExpenses = $expenseBy->except(['salaries', 'supplies', 'utilities'])->sum();

        // ---- Overdue fees ----
        $overdue = Invoice::with(['student.enrollments.schoolClass', 'payments'])->where('status', 'overdue')->get()
            ->sortByDesc(fn ($i) => $i->balance)->values();

        // ---- Latest transactions ----
        $income = Payment::with('invoice.student')->latest('paid_on')->latest('id')->limit(5)->get()->map(fn ($p) => [
            'ref' => $p->invoice->number.' · '.$p->invoice->student->full_name, 'date' => Carbon::parse($p->paid_on),
            'category' => ucfirst($p->invoice->category), 'amount' => (float) $p->amount, 'status' => 'Paid']);
        $outgo = Expense::latest('spent_on')->latest('id')->limit(5)->get()->map(fn ($e) => [
            'ref' => $e->reference.' · '.$e->description, 'date' => Carbon::parse($e->spent_on),
            'category' => ucfirst($e->category), 'amount' => -(float) $e->amount, 'status' => 'Posted']);

        return view('finance.dashboard', [
            'stats' => $stats, 'net' => $net, 'month' => $now->format('F Y'),
            'bars' => $months->all(), 'top' => $top,
            'revenueBy' => $revenueBy, 'expenseBy' => $expenseBy, 'otherExpenses' => $otherExpenses,
            'overdue' => $overdue->take(3), 'overdueTotal' => $overdue->sum(fn ($i) => $i->balance), 'overdueCount' => $overdue->count(),
            'recent' => $income->concat($outgo)->sortByDesc(fn ($r) => $r['date']->timestamp)->take(5)->values(),
            'budgets' => ApprovalRequest::where('type', 'budget')->where('status', 'pending')->latest()->limit(2)->get(),
        ]);
    }
}

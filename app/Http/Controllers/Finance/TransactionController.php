<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionController extends Controller
{
    public const CATEGORIES = ['supplies', 'utilities', 'transport', 'activities', 'other'];

    public function index(Request $request)
    {
        $request->validate(['type' => 'nullable|in:income,expense', 'month' => 'nullable|date_format:Y-m']);
        $from = $request->month ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth() : null;
        $to = $from?->copy()->endOfMonth();

        $income = $request->type === 'expense' ? collect() : Payment::with('invoice.student')
            ->when($from, fn ($q) => $q->whereDate('paid_on', '>=', $from->toDateString())->whereDate('paid_on', '<=', $to->toDateString()))
            ->get()->map(fn ($p) => ['ref' => $p->invoice->number.' · '.$p->invoice->student->full_name, 'date' => Carbon::parse($p->paid_on),
                'category' => ucfirst($p->invoice->category), 'amount' => (float) $p->amount, 'status' => 'Paid', 'id' => $p->id]);

        $outgo = $request->type === 'income' ? collect() : Expense::query()
            ->when($from, fn ($q) => $q->between($from, $to))
            ->get()->map(fn ($e) => ['ref' => $e->reference.' · '.$e->description, 'date' => Carbon::parse($e->spent_on),
                'category' => ucfirst($e->category), 'amount' => -(float) $e->amount, 'status' => 'Posted', 'id' => $e->id]);

        $all = $income->concat($outgo)->sortByDesc(fn ($r) => $r['date']->timestamp * 100000 + $r['id'])->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = new LengthAwarePaginator($all->forPage($page, 15), $all->count(), 15, $page, ['path' => $request->url(), 'query' => $request->query()]);

        return view('finance.transactions', ['rows' => $rows, 'in' => $all->where('amount', '>', 0)->sum('amount'),
            'out' => -$all->where('amount', '<', 0)->sum('amount'), 'categories' => self::CATEGORIES]);
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:'.implode(',', self::CATEGORIES),
            'description' => 'required|string|max:120',
            'amount' => 'required|numeric|min:0.01|max:10000000',
            'spent_on' => 'required|date|before_or_equal:today',
        ]);

        $expense = Expense::create($data + ['reference' => 'EXP-'.str_pad(Expense::max('id') + 1, 4, '0', STR_PAD_LEFT), 'recorded_by' => auth()->id()]);

        return redirect()->route('finance.transactions')->with('status', "Expense {$expense->reference} recorded.");
    }
}

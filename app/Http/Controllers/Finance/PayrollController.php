<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\PayrollRun;
use App\Models\StaffMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index()
    {
        $done = PayrollRun::pluck('month')->map(fn ($m) => $m->format('Y-m'))->all();
        $options = collect(range(0, 5))->map(fn ($i) => now()->subMonths($i)->startOfMonth())->reject(fn ($m) => in_array($m->format('Y-m'), $done))->values();

        return view('finance.payroll', [
            'runs' => PayrollRun::latest('month')->limit(12)->get(),
            'staff' => StaffMember::orderBy('type')->orderBy('first_name')->get(),
            'options' => $options,
            'monthly' => (float) StaffMember::where('status', 'active')->sum('monthly_salary'),
        ]);
    }

    public function process(Request $request)
    {
        $data = $request->validate(['month' => 'required|date_format:Y-m']);
        $month = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
        abort_if($month->isFuture() && $month->format('Y-m') !== now()->format('Y-m'), 422, 'You cannot process a future month.');

        if (PayrollRun::whereDate('month', $month->toDateString())->exists()) {
            return back()->withErrors(['month' => 'Payroll for '.$month->format('F Y').' was already processed.']);
        }
        $staff = StaffMember::where('status', 'active')->get();
        $total = (float) $staff->sum('monthly_salary');
        if ($total <= 0) {
            return back()->withErrors(['month' => 'Set the monthly salaries first (below), then process payroll.']);
        }

        DB::transaction(function () use ($month, $staff, $total) {
            $run = PayrollRun::create(['month' => $month->toDateString(), 'staff_count' => $staff->count(), 'total' => $total, 'processed_by' => auth()->id()]);
            Expense::create([
                'reference' => 'PAY-'.str_pad(Expense::max('id') + 1, 4, '0', STR_PAD_LEFT), 'category' => 'salaries', 'description' => 'Staff payroll',
                'amount' => $total, 'spent_on' => min(now(), $month->copy()->endOfMonth())->toDateString(), 'payroll_run_id' => $run->id, 'recorded_by' => auth()->id(),
            ]);
        });

        return back()->with('status', 'Payroll for '.$month->format('F Y').' processed: $'.number_format($total).' for '.$staff->count().' staff.');
    }

    public function saveSalaries(Request $request)
    {
        $data = $request->validate(['salary' => 'required|array', 'salary.*' => 'nullable|numeric|min:0|max:1000000']);
        foreach ($data['salary'] as $id => $amount) {
            StaffMember::whereKey($id)->update(['monthly_salary' => $amount ?: 0]);
        }

        return back()->with('status', 'Salaries saved.');
    }
}

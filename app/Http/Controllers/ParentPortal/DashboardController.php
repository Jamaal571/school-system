<?php

namespace App\Http\Controllers\ParentPortal;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Payment;
use App\Models\SchoolEvent;
use Illuminate\Http\Request;

class DashboardController extends ParentBaseController
{
    public function index(Request $request)
    {
        $child = $this->child($request);
        $this->markOverdue();
        $term = AcademicYear::current()?->currentTerm();
        $enrollment = $child->enrollments()->with('schoolClass.homeroomTeacher')->latest('academic_year_id')->first();
        $class = $enrollment?->schoolClass;

        // ---- Grades ----
        $grades = $this->grades($child);
        $average = $grades->isEmpty() ? null : round($grades->avg('pct'));
        $ordered = $grades->sortBy('due')->values();
        $trend = $ordered->count() > 1 ? round($ordered->last()['pct'] - $ordered[$ordered->count() - 2]['pct']) : null;

        // ---- Attendance: term to date and this week ----
        $records = Attendance::where('student_id', $child->id)->whereDate('date', '>=', ($term?->starts_on ?? now()->startOfYear())->toDateString())->get();
        $counts = $records->countBy('status');
        $attended = ($counts['present'] ?? 0) + ($counts['late'] ?? 0);
        $rate = $records->isEmpty() ? null : round($attended / $records->count() * 100, 1);

        $byDate = Attendance::where('student_id', $child->id)->whereDate('date', '>=', now()->startOfWeek()->toDateString())->whereDate('date', '<=', now()->startOfWeek()->addDays(4)->toDateString())
            ->get()->keyBy(fn ($r) => $r->date->toDateString());
        $week = collect(range(0, 4))->map(function ($i) use ($byDate) {
            $d = now()->startOfWeek()->addDays($i);

            return ['label' => $d->format('D j'), 'status' => $d->isFuture() ? 'upcoming' : ($byDate[$d->toDateString()]->status ?? 'none')];
        });
        $today = $byDate[today()->toDateString()]->status ?? null;

        // ---- Upcoming assignments ----
        $upcoming = $class ? Assignment::with('subject')->where('school_class_id', $class->id)->whereDate('due_on', '>=', today())
            ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $child->id)->whereNotNull('submitted_on'))->orderBy('due_on')->get() : collect();

        // ---- Fees ----
        $invoices = Invoice::with('payments')->where('student_id', $child->id)->get();
        $unpaid = $invoices->where('status', '!=', 'paid')->sortBy(fn ($i) => ($i->status === 'overdue' ? '0' : '1').$i->due_on->toDateString())->values();
        $primary = $unpaid->first();
        $lastPayment = Payment::with('invoice')->whereIn('invoice_id', $invoices->pluck('id'))->latest('paid_on')->latest('id')->first();

        return view('parent.dashboard', [
            'child' => $child, 'children' => $this->children(), 'class' => $class, 'term' => $term, 'today' => $today,
            'average' => $average, 'trend' => $trend, 'subjects' => $this->subjects($grades), 'rate' => $rate, 'counts' => $counts,
            'week' => $week, 'upcoming' => $upcoming, 'unpaid' => $unpaid, 'primary' => $primary,
            'outstanding' => $unpaid->sum(fn ($i) => $i->balance), 'lastPayment' => $lastPayment,
            'messages' => Message::with(['sender', 'recipient'])->where(fn ($q) => $q->where('from_user_id', auth()->id())->orWhere('to_user_id', auth()->id()))->latest('id')->limit(2)->get(),
            'events' => SchoolEvent::whereIn('audience', ['all', 'parents'])->upcoming()->limit(2)->get(),
            'notice' => Announcement::whereIn('audience', ['all', 'parents'])->latest()->first(),
        ]);
    }
}

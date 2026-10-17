<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StaffMember;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ---- Signed-in user (shown in the sidebar and greeting) ----
        $user = auth()->user();
        $parts = array_values(array_filter(explode(' ', $user->name), fn ($p) => ! str_ends_with($p, '.')));
        $head = [
            'name' => $user->name,
            'short' => str_starts_with($user->name, 'Dr.') ? 'Dr. '.end($parts) : $parts[0],
            'initials' => strtoupper(mb_substr($parts[0], 0, 1).(count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '')),
            'role' => $user->role === 'admin' ? 'Head of School' : ucfirst($user->role),
        ];
        view()->share('head', $head);

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        // ---- Attendance: last 5 school days that have records ----
        $days = Attendance::selectRaw("date, count(*) as total, sum(case when status in ('present','late') then 1 else 0 end) as attended")
            ->groupBy('date')->orderByDesc('date')->limit(5)->get()->reverse()->values();
        $dates = $days->map(fn ($d) => Carbon::parse($d->date));
        $rates = $days->map(fn ($d) => round($d->attended / $d->total * 100, 1))->values();
        $latest = $days->last();
        $rate = $rates->last();
        $prev = $rates->count() > 1 ? $rates[$rates->count() - 2] : null;
        $diff = $prev === null ? null : round($rate - $prev, 1);

        $attendance = [
            'labels' => $dates->map(fn ($d) => $d->format('D j'))->all(),
            'points' => $rates->all(),
            'forecast' => [],
            'range' => $dates->count()
                ? $dates->first()->format('j').'–'.$dates->last()->format('j F Y')
                : 'No attendance recorded yet',
            'tooltip' => $latest ? number_format($rate, 1).'% '.($dates->last()->isToday() ? 'today' : 'latest') : null,
        ];

        // ---- Week / Month switch for the chart ----
        $range = $request->query('range') === 'month' ? 'month' : 'week';
        if ($range === 'month') {
            $weeks = Attendance::selectRaw("date, count(*) as total, sum(case when status in ('present','late') then 1 else 0 end) as attended")
                ->whereDate('date', '>=', now()->subDays(27)->toDateString())
                ->groupBy('date')->orderBy('date')->get()
                ->groupBy(fn ($d) => Carbon::parse($d->date)->startOfWeek()->toDateString());
            $weekRates = $weeks->map(fn ($g) => round($g->sum('attended') / max($g->sum('total'), 1) * 100, 1));
            $attendance['labels'] = $weekRates->keys()->map(fn ($d) => 'Wk '.Carbon::parse($d)->format('j M'))->all();
            $attendance['points'] = $weekRates->values()->all();
            $attendance['range'] = $weekRates->count() ? 'last 4 weeks, weekly average' : 'No attendance recorded yet';
            $attendance['tooltip'] = $weekRates->count() ? number_format($weekRates->last(), 1).'% this week' : null;
        }

        // ---- Enrollment by year (based on admission dates) ----
        $students = Student::where('status', 'active')->count();
        $thisYear = now()->year;
        $years = range($thisYear - 3, $thisYear);
        $counts = collect($years)->map(fn ($y) => $y === $thisYear
            ? $students
            : Student::whereDate('admitted_on', '<=', "$y-12-31")->where('status', '!=', 'withdrawn')->count())->values();

        $yMin = (int) (floor($counts->min() / 100) * 100);
        $yMax = (int) (ceil($counts->max() / 100) * 100);
        if ($yMax - $yMin < 200) {
            $yMax = $yMin + 200;
        }
        $before = $counts[$counts->count() - 2];
        $yoy = $before > 0 ? round(($students - $before) / $before * 100, 1) : null;

        $enrollment = [
            'labels' => array_map('strval', $years),
            'points' => $counts->all(),
            'current' => $students,
            'yMin' => $yMin,
            'yMax' => $yMax,
            'ticks' => [$yMin, ($yMin + $yMax) / 2, $yMax],
            'yoy' => $yoy === null ? '' : sprintf('%+.1f%% YoY', $yoy),
        ];

        // ---- Stat cards ----
        $teachers = StaffMember::where('type', 'teacher')->count();
        $support = StaffMember::where('type', '!=', 'teacher')->count();
        $collected = (float) Payment::sum('amount');
        $invoiced = (float) Invoice::sum('amount');
        $collectedPct = $invoiced > 0 ? round($collected / $invoiced * 100, 1) : 0;

        $stats = [
            [
                'label' => 'Active students', 'value' => number_format($students), 'icon' => 'academic-cap',
                'note' => $yoy === null ? 'Enrolled this academic year' : number_format(abs($yoy), 1).'% from last academic year',
                'tone' => $yoy === null ? 'muted' : ($yoy >= 0 ? 'success' : 'danger'),
                'arrow' => $yoy === null ? false : ($yoy >= 0 ? true : 'down'),
            ],
            [
                'label' => 'Staff & teachers', 'value' => number_format($teachers + $support), 'icon' => 'users',
                'note' => "$teachers teachers · $support support staff", 'tone' => 'muted', 'arrow' => false,
            ],
            [
                'label' => 'Daily attendance', 'value' => $latest ? number_format($rate, 1).'%' : '—', 'icon' => 'calendar-days',
                'note' => $diff === null ? 'No previous school day yet' : number_format(abs($diff), 1).' pts vs. previous school day',
                'tone' => $diff === null ? 'muted' : ($diff >= 0 ? 'success' : 'danger'),
                'arrow' => $diff === null ? false : ($diff >= 0 ? true : 'down'),
            ],
            [
                'label' => 'Term revenue', 'value' => '$'.number_format($collected), 'icon' => 'wallet',
                'note' => "$collectedPct% of $".number_format($invoiced).' invoiced', 'tone' => 'primary', 'arrow' => false,
            ],
        ];

        // ---- Approvals ----
        $pendingQuery = fn () => ApprovalRequest::pending()->orderByRaw("priority = 'urgent' desc")->orderBy('id');
        $approvalCounts = [
            'pending' => ApprovalRequest::pending()->count(),
            'urgent' => ApprovalRequest::pending()->where('priority', 'urgent')->count(),
        ];
        $approvals = $pendingQuery()->take(4)->get()->map(fn ($a) => [
            'title' => $a->title,
            'by' => $a->department,
            'value' => $a->amount !== null
                ? '$'.number_format($a->amount)
                : ($a->starts_on
                    ? $a->starts_on->format('j').($a->ends_on ? '–'.$a->ends_on->format('j M') : ' '.$a->starts_on->format('M'))
                    : '—'),
            'status' => $a->priority === 'urgent' ? 'urgent' : 'pending',
        ])->all();

        // ---- Activity feed (built from real records) ----
        $activity = [];
        if ($top = $pendingQuery()->first()) {
            $activity[] = [
                'icon' => $top->type === 'budget' ? 'wallet' : 'clipboard-document-check', 'tone' => 'warning',
                'title' => ucfirst($top->type).' request needs approval',
                'text' => $top->amount !== null
                    ? "$top->department requested $".number_format($top->amount).' for '.strtolower($top->title).'.'
                    : "$top->department: $top->title.",
                'meta' => $top->created_at->format('g:i A')." · $top->department",
            ];
        }
        $overdue = Invoice::where('status', 'overdue');
        if (($overdueCount = (clone $overdue)->count()) > 0) {
            $activity[] = [
                'icon' => 'shield-exclamation', 'tone' => 'danger', 'title' => 'Invoices overdue',
                'text' => "$overdueCount invoices totalling $".number_format((float) (clone $overdue)->sum('amount')).' are past due.',
                'meta' => 'Finance',
            ];
        }
        if ($latest) {
            $classCount = Attendance::whereDate('date', $dates->last()->toDateString())->distinct()->count('school_class_id');
            $activity[] = [
                'icon' => 'check-circle', 'tone' => 'success',
                'title' => $dates->last()->isToday() ? 'Attendance complete' : 'Latest attendance',
                'text' => number_format($latest->attended).' of '.number_format($latest->total)." students present across $classCount classes.",
                'meta' => $dates->last()->format('D j M').' · Attendance office',
            ];
        }

        return view('admin.dashboard', compact(
            'greeting', 'head', 'stats', 'attendance', 'enrollment', 'approvals', 'approvalCounts', 'activity', 'range'
        ));
    }
}

<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\TimetableSlot;
use Carbon\Carbon;

class DashboardController extends TeacherBaseController
{
    public function index()
    {
        $me = $this->me();
        $classes = $this->myClasses();
        $mine = fn ($q) => $q->where('staff_member_id', $me->id);

        // ---- Schedule: today, or the next school day on weekends ----
        $isToday = now()->isWeekday();
        $day = $isToday ? now() : now()->nextWeekday();
        $slots = TimetableSlot::with(['schoolClass' => fn ($q) => $q->withCount('enrollments'), 'subject'])
            ->where('staff_member_id', $me->id)->where('day_of_week', $day->dayOfWeekIso)->orderBy('starts_at')->get();

        $nowTime = now()->format('H:i:s');
        $nextFound = false;
        $schedule = $slots->map(function ($s) use ($isToday, $nowTime, &$nextFound) {
            $status = 'upcoming';
            if ($isToday && $s->ends_at <= $nowTime) {
                $status = 'complete';
            } elseif ($isToday && ! $nextFound) {
                $status = 'next';
                $nextFound = true;
            }

            return [
                'status' => $status, 'time' => Carbon::parse($s->starts_at)->format('H:i'),
                'class' => $s->schoolClass->name, 'class_id' => $s->school_class_id, 'subject' => $s->subject->name,
                'topic' => $s->topic, 'room' => $s->room, 'students' => $s->schoolClass->enrollments_count,
            ];
        });
        $next = $schedule->firstWhere('status', 'next');
        $nextLine = ! $isToday ? 'No classes today. Your next school day is '.$day->format('l').'.'
            : ($next ? "Your next class starts at {$next['time']}." : ($schedule->isEmpty() ? 'You have no classes today.' : 'Your classes for today are finished.'));

        // ---- Grading ----
        $ungraded = Submission::whereHas('assignment', $mine)->whereNotNull('submitted_on')->whereNull('score');
        $toGrade = (clone $ungraded)->count();
        $activeAssignments = (clone $ungraded)->distinct()->count('assignment_id');

        $graded = Submission::with('assignment')->whereHas('assignment', $mine)->whereNotNull('score')->get()
            ->map(fn ($s) => ['student' => $s->student_id, 'class' => $s->assignment->school_class_id, 'assignment' => $s->assignment_id,
                'due' => $s->assignment->due_on->toDateString(), 'pct' => $s->score / max($s->assignment->max_score, 1) * 100]);
        $average = $graded->isEmpty() ? null : round($graded->avg('pct'));

        $byAssignment = $graded->groupBy('assignment')->map(fn ($g) => ['due' => $g->first()['due'], 'avg' => $g->avg('pct')])->sortBy('due')->values();
        $trend = $byAssignment->count() > 1 ? round($byAssignment[$byAssignment->count() - 1]['avg'] - $byAssignment[$byAssignment->count() - 2]['avg']) : null;

        $progress = $classes->map(fn ($c) => [
            'name' => $c->name, 'pct' => ($g = $graded->where('class', $c->id))->isEmpty() ? null : round($g->avg('pct')),
        ])->filter(fn ($r) => $r['pct'] !== null)->values();
        $needSupport = $graded->groupBy('student')->filter(fn ($g) => $g->avg('pct') < 60)->count();

        $queue = Assignment::with('schoolClass')->where('staff_member_id', $me->id)
            ->withCount(['submissions as to_grade' => fn ($q) => $q->whereNotNull('submitted_on')->whereNull('score')])
            ->withMin(['submissions as first_submitted' => fn ($q) => $q->whereNotNull('submitted_on')->whereNull('score')], 'submitted_on')
            ->get()->filter(fn ($a) => $a->to_grade > 0)->sortBy('first_submitted')->take(5)->values();

        $deadline = Assignment::with('schoolClass')->where('staff_member_id', $me->id)->whereDate('due_on', '>=', today())->orderBy('due_on')->first();

        // ---- Stat cards ----
        $studentCount = Enrollment::whereIn('school_class_id', $classes->pluck('id'))->distinct()->count('student_id');
        $levels = $classes->pluck('grade_level');
        $subjects = $slots->pluck('subject.name')->unique()->implode(', ');
        $complete = $schedule->where('status', 'complete')->count();

        $stats = [
            ['label' => 'Assigned classes today', 'value' => $schedule->count(), 'icon' => 'presentation-screen', 'tone' => 'primary',
             'note' => $isToday ? "$complete complete · ".($schedule->count() - $complete).' upcoming' : 'Showing '.$day->format('l')],
            ['label' => 'Assignments to grade', 'value' => $toGrade, 'icon' => 'clipboard-document-check', 'tone' => 'warning',
             'note' => "Across $activeAssignments active ".\Illuminate\Support\Str::plural('assignment', $activeAssignments)],
            ['label' => 'Students in your classes', 'value' => $studentCount, 'icon' => 'users', 'tone' => 'muted',
             'note' => $levels->isEmpty() ? 'No classes yet' : 'Grade '.$levels->min().($levels->min() === $levels->max() ? '' : '–'.$levels->max()).($subjects ? " · $subjects" : '')],
            ['label' => 'Average class score', 'value' => $average === null ? '—' : $average.'%', 'icon' => 'presentation-chart-line',
             'tone' => $trend === null ? 'muted' : ($trend >= 0 ? 'success' : 'danger'),
             'note' => $trend === null ? 'From graded work' : abs($trend).' pts since last assessment',
             'arrow' => $trend === null ? false : ($trend >= 0 ? true : 'down')],
        ];

        $hour = now()->hour;

        return view('teacher.dashboard', [
            'me' => $me, 'greeting' => $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening'),
            'nextLine' => $nextLine, 'isToday' => $isToday, 'day' => $day, 'schedule' => $schedule, 'stats' => $stats,
            'progress' => $progress, 'needSupport' => $needSupport, 'queue' => $queue, 'deadline' => $deadline,
            'announcements' => Announcement::whereIn('audience', ['all', 'teachers'])->latest()->limit(3)->get(),
        ]);
    }
}

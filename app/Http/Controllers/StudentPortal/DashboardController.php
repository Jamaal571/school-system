<?php

namespace App\Http\Controllers\StudentPortal;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Message;
use App\Models\SchoolEvent;
use App\Models\Submission;
use App\Models\TimetableSlot;
use Carbon\Carbon;

class DashboardController extends StudentBaseController
{
    public function index()
    {
        $me = $this->me();
        $class = $this->classOf($me);
        $term = AcademicYear::current()?->currentTerm();

        // ---- Timetable: today, or the next school day on weekends ----
        $isToday = now()->isWeekday();
        $day = $isToday ? now() : now()->nextWeekday();
        $slots = $class ? TimetableSlot::with(['subject', 'teacher'])->where('school_class_id', $class->id)->where('day_of_week', $day->dayOfWeekIso)->orderBy('starts_at')->get() : collect();
        $nowTime = now()->format('H:i:s');
        $nextFound = false;
        $schedule = $slots->map(function ($s) use ($isToday, $nowTime, &$nextFound) {
            $status = 'upcoming';
            if ($isToday && $s->ends_at <= $nowTime) {
                $status = 'complete';
            } elseif ($isToday && ! $nextFound) {
                $status = 'next';
                $nextFound = true;
            } elseif (! $isToday && ! $nextFound) {
                $nextFound = true;
                $status = 'next';
            }

            return ['status' => $status, 'start' => Carbon::parse($s->starts_at), 'end' => Carbon::parse($s->ends_at), 'subject' => $s->subject->name, 'subject_id' => $s->subject_id,
                'room' => $s->room, 'teacher' => $s->teacher?->full_name, 'topic' => $s->topic];
        });
        $next = $schedule->firstWhere('status', 'next');
        $complete = $schedule->where('status', 'complete')->count();

        // ---- Grades and attendance ----
        $grades = $this->grades($me);
        $average = $grades->isEmpty() ? null : round($grades->avg('pct'));
        $ordered = $grades->sortBy('due')->values();
        $trend = $ordered->count() > 1 ? round($ordered->last()['pct'] - $ordered[$ordered->count() - 2]['pct']) : null;
        $feedback = $grades->filter(fn ($g) => filled($g['feedback']))->sortBy('due')->last();

        $records = Attendance::where('student_id', $me->id)->whereDate('date', '>=', ($term?->starts_on ?? now()->startOfYear())->toDateString())->get();
        $attended = $records->whereIn('status', ['present', 'late'])->count();
        $rate = $records->isEmpty() ? null : round($attended / $records->count() * 100, 1);

        // ---- Assignments ----
        $subs = Submission::where('student_id', $me->id)->get()->keyBy('assignment_id');
        $open = $class ? Assignment::with('subject')->where('school_class_id', $class->id)->orderBy('due_on')->get()
            ->filter(fn ($a) => ! ($subs[$a->id]->submitted_on ?? null) && ($a->due_on->isFuture() || $a->due_on->isToday() || $a->due_on->gte(now()->subDays(7)))) : collect();
        $upcoming = $open->filter(fn ($a) => $a->due_on->gte(today()));
        $focus = $open->take(4)->map(fn ($a) => ['a' => $a, 'status' => $this->status($a, $subs[$a->id] ?? null)]);
        $lastWeek = $class ? Assignment::where('school_class_id', $class->id)->whereDate('due_on', '>=', now()->subDays(7)->toDateString())->whereDate('due_on', '<', today())->get() : collect();
        $missed = $lastWeek->filter(fn ($a) => ! ($subs[$a->id]->submitted_on ?? null))->count();

        // ---- Messages and announcements ----
        $feed = Message::with('sender')->where('to_user_id', auth()->id())->latest('id')->limit(2)->get()
            ->map(fn ($m) => ['type' => 'message', 'title' => $m->sender->name, 'text' => $m->body, 'meta' => $m->created_at->format('j M').($m->read_at ? '' : ' · Unread'), 'at' => $m->created_at, 'link' => route('student.messages.show', $m->sender)])
            ->concat(Announcement::whereIn('audience', ['all', 'students'])->latest()->limit(2)->get()
                ->map(fn ($a) => ['type' => 'announcement', 'title' => $a->title, 'text' => $a->body, 'meta' => $a->created_at->format('j M').' · School announcement', 'at' => $a->created_at, 'link' => route('student.announcements')]))
            ->sortByDesc(fn ($r) => $r['at']->timestamp)->take(3)->values();

        return view('student.dashboard', [
            'me' => $me, 'class' => $class, 'isToday' => $isToday, 'day' => $day, 'schedule' => $schedule, 'next' => $next, 'complete' => $complete,
            'average' => $average, 'trend' => $trend, 'rate' => $rate, 'attended' => $attended, 'recorded' => $records->count(),
            'subjects' => $this->subjects($grades), 'feedback' => $feedback, 'upcoming' => $upcoming, 'focus' => $focus, 'missed' => $missed, 'lastWeek' => $lastWeek->count(),
            'feed' => $feed, 'event' => SchoolEvent::where('audience', 'all')->upcoming()->first(),
        ]);
    }
}

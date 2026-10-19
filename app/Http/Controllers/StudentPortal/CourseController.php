<?php

namespace App\Http\Controllers\StudentPortal;

use App\Models\Assignment;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\TimetableSlot;

class CourseController extends StudentBaseController
{
    public function index()
    {
        $me = $this->me();
        $courses = $this->courses($me);
        $grades = $this->grades($me)->groupBy('subject');

        return view('student.courses', ['courses' => $courses, 'averages' => $grades->map(fn ($g) => round($g->avg('pct')))]);
    }

    public function show(Subject $subject)
    {
        $me = $this->me();
        $class = $this->classOf($me);
        $course = $this->courses($me)->firstWhere('subject.id', $subject->id) ?? abort(404);

        $assignments = Assignment::where('school_class_id', $class->id)->where('subject_id', $subject->id)->orderByDesc('due_on')->get();
        $subs = Submission::where('student_id', $me->id)->whereIn('assignment_id', $assignments->pluck('id'))->get()->keyBy('assignment_id');

        return view('student.course', [
            'subject' => $subject, 'course' => $course,
            'rows' => $assignments->map(fn ($a) => ['a' => $a, 'sub' => $subs[$a->id] ?? null, 'status' => $this->status($a, $subs[$a->id] ?? null)]),
            'slots' => TimetableSlot::where('school_class_id', $class->id)->where('subject_id', $subject->id)->orderBy('day_of_week')->get(),
        ]);
    }

    /** The subjects this student studies, with their teacher. */
    private function courses($me)
    {
        $class = $this->classOf($me);
        if (! $class) {
            return collect();
        }
        $fromSlots = TimetableSlot::with(['subject', 'teacher'])->where('school_class_id', $class->id)->get()->map(fn ($s) => ['subject' => $s->subject, 'teacher' => $s->teacher?->full_name]);
        $fromWork = Assignment::with(['subject', 'teacher'])->where('school_class_id', $class->id)->get()->map(fn ($a) => ['subject' => $a->subject, 'teacher' => $a->teacher?->full_name]);

        return $fromSlots->concat($fromWork)->unique(fn ($c) => $c['subject']->id)->sortBy(fn ($c) => $c['subject']->name)->values();
    }
}

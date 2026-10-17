<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Submission;

class ClassController extends TeacherBaseController
{
    public function index()
    {
        $classes = $this->myClasses();
        $counts = \App\Models\Enrollment::whereIn('school_class_id', $classes->pluck('id'))
            ->selectRaw('school_class_id, count(*) as total')->groupBy('school_class_id')->pluck('total', 'school_class_id');

        return view('teacher.classes.index', compact('classes', 'counts'));
    }

    public function show(SchoolClass $class)
    {
        abort_unless($this->myClasses()->contains('id', $class->id), 403, 'This is not one of your classes.');

        $students = $class->students()->orderBy('first_name')->get();
        $attendance = Attendance::where('school_class_id', $class->id)->whereDate('date', '>=', now()->subDays(30)->toDateString())
            ->selectRaw("student_id, count(*) as total, sum(case when status in ('present','late') then 1 else 0 end) as attended")
            ->groupBy('student_id')->get()->keyBy('student_id');
        $scores = Submission::with('assignment')->whereNotNull('score')
            ->whereHas('assignment', fn ($q) => $q->where('school_class_id', $class->id)->where('staff_member_id', $this->me()->id))
            ->get()->groupBy('student_id')->map(fn ($g) => round($g->avg(fn ($s) => $s->score / max($s->assignment->max_score, 1) * 100)));

        return view('teacher.classes.show', compact('class', 'students', 'attendance', 'scores'));
    }
}

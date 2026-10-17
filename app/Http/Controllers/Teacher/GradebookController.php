<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;

class GradebookController extends TeacherBaseController
{
    public function index(Request $request)
    {
        $classes = $this->myClasses();
        $class = $classes->firstWhere('id', (int) $request->class) ?? $classes->first();

        $assignments = $class
            ? Assignment::where('staff_member_id', $this->me()->id)->where('school_class_id', $class->id)->orderBy('due_on')->get()
            : collect();
        $students = $class ? $class->students()->orderBy('first_name')->get() : collect();
        $subs = Submission::whereIn('assignment_id', $assignments->pluck('id'))->get()->groupBy('student_id')->map->keyBy('assignment_id');

        return view('teacher.gradebook', compact('classes', 'class', 'assignments', 'students', 'subs'));
    }
}

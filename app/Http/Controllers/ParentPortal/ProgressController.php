<?php

namespace App\Http\Controllers\ParentPortal;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;

class ProgressController extends ParentBaseController
{
    public function index(Request $request)
    {
        $child = $this->child($request);
        $classId = $child->enrollments()->latest('academic_year_id')->value('school_class_id');

        return view('parent.progress', [
            'child' => $child, 'children' => $this->children(), 'subjects' => $this->subjects($this->grades($child)),
            'assignments' => Assignment::with(['subject', 'teacher'])->where('school_class_id', $classId)->orderByDesc('due_on')->get(),
            'subs' => Submission::where('student_id', $child->id)->get()->keyBy('assignment_id'),
        ]);
    }
}

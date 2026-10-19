<?php

namespace App\Http\Controllers\StudentPortal;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;

class AssignmentController extends StudentBaseController
{
    public function index(Request $request)
    {
        $me = $this->me();
        $class = $this->classOf($me);
        $show = in_array($request->show, ['todo', 'done', 'all'], true) ? $request->show : 'todo';

        $subs = Submission::where('student_id', $me->id)->get()->keyBy('assignment_id');
        $rows = ($class ? Assignment::with(['subject', 'teacher'])->where('school_class_id', $class->id)->orderBy('due_on')->get() : collect())
            ->map(fn ($a) => ['a' => $a, 'sub' => $subs[$a->id] ?? null, 'status' => $this->status($a, $subs[$a->id] ?? null)])
            ->filter(fn ($r) => match ($show) {
                'todo' => ! ($r['sub']->submitted_on ?? null),
                'done' => (bool) ($r['sub']->submitted_on ?? false),
                default => true,
            })->values();

        return view('student.assignments', ['rows' => $show === 'done' ? $rows->reverse()->values() : $rows, 'show' => $show]);
    }

    public function show(Assignment $assignment)
    {
        $me = $this->me();
        abort_unless($assignment->school_class_id === $this->classOf($me)?->id, 403, 'This assignment is not for your class.');
        $sub = Submission::where('student_id', $me->id)->where('assignment_id', $assignment->id)->first();

        return view('student.assignment', ['assignment' => $assignment->load(['subject', 'teacher']), 'sub' => $sub, 'status' => $this->status($assignment, $sub)]);
    }
}

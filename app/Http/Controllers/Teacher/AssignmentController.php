<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;

class AssignmentController extends TeacherBaseController
{
    public function index(Request $request)
    {
        $assignments = Assignment::with('schoolClass')->where('staff_member_id', $this->me()->id)
            ->when($request->class, fn ($q, $c) => $q->where('school_class_id', $c))
            ->withCount(['submissions as submitted_count' => fn ($q) => $q->whereNotNull('submitted_on'),
                         'submissions as graded_count' => fn ($q) => $q->whereNotNull('score')])
            ->orderByDesc('due_on')->paginate(12)->withQueryString();

        $sizes = Enrollment::whereIn('school_class_id', $assignments->pluck('school_class_id'))
            ->selectRaw('school_class_id, count(*) as total')->groupBy('school_class_id')->pluck('total', 'school_class_id');

        return view('teacher.assignments.index', ['assignments' => $assignments, 'classes' => $this->myClasses(), 'sizes' => $sizes]);
    }

    public function create()
    {
        return view('teacher.assignments.create', ['classes' => $this->myClasses()]);
    }

    public function store(Request $request)
    {
        $me = $this->me();
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'school_class_id' => 'required|in:'.$this->myClasses()->pluck('id')->implode(','),
            'due_on' => 'required|date|after_or_equal:today',
            'max_score' => 'required|integer|min:1|max:1000',
        ]);

        $subjectId = TimetableSlot::where('staff_member_id', $me->id)->where('school_class_id', $data['school_class_id'])->value('subject_id')
            ?? Subject::value('id');
        abort_if(! $subjectId, 422, 'Add a subject in Academics first.');

        Assignment::create($data + ['subject_id' => $subjectId, 'staff_member_id' => $me->id]);

        return redirect()->route('teacher.assignments')->with('status', "Assignment “{$data['title']}” created.");
    }

    public function grade(Assignment $assignment)
    {
        $this->authorizeOwner($assignment);

        return view('teacher.assignments.grade', [
            'assignment' => $assignment->load('schoolClass'),
            'rows' => Enrollment::with('student')->where('school_class_id', $assignment->school_class_id)->get()
                ->sortBy(fn ($e) => $e->student->first_name)->values(),
            'subs' => $assignment->submissions()->get()->keyBy('student_id'),
        ]);
    }

    public function saveGrades(Request $request, Assignment $assignment)
    {
        $this->authorizeOwner($assignment);
        $data = $request->validate([
            'scores' => 'nullable|array', 'scores.*' => "nullable|numeric|min:0|max:{$assignment->max_score}",
            'feedback' => 'nullable|array', 'feedback.*' => 'nullable|string|max:300',
        ]);

        $enrolled = Enrollment::where('school_class_id', $assignment->school_class_id)->pluck('student_id')->map(fn ($id) => (int) $id)->all();
        $saved = 0;
        foreach ($data['scores'] ?? [] as $studentId => $score) {
            if ($score === null || $score === '' || ! in_array((int) $studentId, $enrolled, true)) {
                continue;
            }
            $submission = Submission::firstOrNew(['assignment_id' => $assignment->id, 'student_id' => $studentId]);
            $submission->submitted_on ??= now()->toDateString();
            $submission->score = $score;
            $submission->feedback = filled($data['feedback'][$studentId] ?? null) ? $data['feedback'][$studentId] : $submission->feedback;
            $submission->graded_at = now();
            $submission->save();
            $saved++;
        }

        return redirect()->route('teacher.assignments.grade', $assignment)->with('status', "$saved ".\Illuminate\Support\Str::plural('score', $saved).' saved.');
    }

    private function authorizeOwner(Assignment $assignment): void
    {
        abort_unless($assignment->staff_member_id === $this->me()->id, 403, 'This is not your assignment.');
    }
}

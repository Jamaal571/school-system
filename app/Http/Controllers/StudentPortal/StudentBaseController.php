<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Submission;

abstract class StudentBaseController extends Controller
{
    protected function me(): Student
    {
        return Student::where('user_id', auth()->id())->first()
            ?? abort(403, 'Your login is not linked to a student record yet. Ask the school office.');
    }

    protected function classOf(Student $student): ?SchoolClass
    {
        return $student->enrollments()->with('schoolClass.homeroomTeacher')->latest('academic_year_id')->first()?->schoolClass;
    }

    protected function letter(float $percent): string
    {
        return $percent >= 90 ? 'A' : ($percent >= 80 ? 'B' : ($percent >= 70 ? 'C' : ($percent >= 60 ? 'D' : 'F')));
    }

    /** Graded work, one row per assignment. */
    protected function grades(Student $student)
    {
        return Submission::with(['assignment.subject', 'assignment.teacher'])->where('student_id', $student->id)->whereNotNull('score')->get()
            ->map(fn ($s) => [
                'id' => $s->assignment_id, 'subject' => $s->assignment->subject->name, 'teacher' => $s->assignment->teacher?->full_name, 'title' => $s->assignment->title,
                'score' => (float) $s->score, 'max' => $s->assignment->max_score, 'due' => $s->assignment->due_on->timestamp, 'feedback' => $s->feedback,
                'pct' => (float) $s->score / max($s->assignment->max_score, 1) * 100,
            ]);
    }

    protected function subjects($grades)
    {
        return $grades->groupBy('subject')->map(function ($g, $name) {
            $latest = $g->sortBy('due')->last();

            return ['subject' => $name, 'teacher' => $latest['teacher'], 'avg' => round($g->avg('pct')), 'letter' => $this->letter($g->avg('pct')), 'latest' => $latest];
        })->sortKeys()->values();
    }

    /** [label, badge key] describing where an assignment stands for this student. */
    protected function status($assignment, $submission): array
    {
        if ($submission && $submission->score !== null) {
            return ['Graded '.rtrim(rtrim(number_format((float) $submission->score, 1), '0'), '.').'/'.$assignment->max_score, 'paid'];
        }
        if ($submission?->submitted_on) {
            return ['Handed in', 'pending'];
        }
        $due = $assignment->due_on;

        return $due->isPast() && ! $due->isToday() ? ['Overdue', 'overdue']
            : ($due->isToday() ? ['Due today', 'urgent'] : ($due->isTomorrow() ? ['Due tomorrow', 'urgent'] : ['Upcoming', 'neutral']));
    }
}

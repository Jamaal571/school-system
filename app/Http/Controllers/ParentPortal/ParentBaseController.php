<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;

abstract class ParentBaseController extends Controller
{
    protected function guardian(): Guardian
    {
        return Guardian::where('user_id', auth()->id())->first()
            ?? abort(403, 'Your login is not linked to a student yet. Ask the school office.');
    }

    protected function children()
    {
        return $this->guardian()->students()->orderBy('first_name')->get();
    }

    /** The child being viewed: ?child=ID, else the last one viewed, else the first. */
    protected function child(Request $request): Student
    {
        $children = $this->children();
        abort_if($children->isEmpty(), 403, 'No student is linked to your login yet. Ask the school office.');

        $child = $children->firstWhere('id', (int) $request->query('child', session('parent_child'))) ?? $children->first();
        session(['parent_child' => $child->id]);

        return $child;
    }

    protected function ownsStudent(int $studentId): void
    {
        abort_unless($this->children()->contains('id', $studentId), 403, 'This is not your child.');
    }

    protected function letter(float $percent): string
    {
        return $percent >= 90 ? 'A' : ($percent >= 80 ? 'B' : ($percent >= 70 ? 'C' : ($percent >= 60 ? 'D' : 'F')));
    }

    /** Graded work for a child, one row per assignment. */
    protected function grades(Student $child)
    {
        return \App\Models\Submission::with(['assignment.subject', 'assignment.teacher'])->where('student_id', $child->id)->whereNotNull('score')->get()
            ->map(fn ($s) => [
                'subject' => $s->assignment->subject->name, 'teacher' => $s->assignment->teacher?->full_name, 'title' => $s->assignment->title,
                'score' => (float) $s->score, 'max' => $s->assignment->max_score, 'due' => $s->assignment->due_on->timestamp,
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

    protected function markOverdue(): void
    {
        Invoice::where('status', 'pending')->whereDate('due_on', '<', today())->update(['status' => 'overdue']);
    }
}

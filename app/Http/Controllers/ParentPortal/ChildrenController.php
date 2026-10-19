<?php

namespace App\Http\Controllers\ParentPortal;

use App\Models\Student;
use Illuminate\Http\Request;

class ChildrenController extends ParentBaseController
{
    public function index()
    {
        $children = $this->children()->load(['enrollments.schoolClass', 'invoices.payments']);
        $stats = $children->mapWithKeys(function ($c) {
            $g = $this->grades($c);
            $att = $c->attendances()->selectRaw("count(*) as total, sum(case when status in ('present','late') then 1 else 0 end) as attended")->first();

            return [$c->id => [
                'average' => $g->isEmpty() ? null : round($g->avg('pct')),
                'attendance' => $att->total ? round($att->attended / $att->total * 100, 1) : null,
                'owing' => $c->invoices->where('status', '!=', 'paid')->sum(fn ($i) => $i->balance),
            ]];
        });

        return view('parent.children', compact('children', 'stats'));
    }

    public function show(Student $student)
    {
        $this->ownsStudent($student->id);
        session(['parent_child' => $student->id]);

        return view('parent.child', [
            'student' => $student->load(['guardians', 'enrollments.schoolClass.homeroomTeacher']),
            'children' => $this->children(), 'child' => $student,
        ]);
    }
}

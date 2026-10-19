<?php

namespace App\Http\Controllers\StudentPortal;

class GradesController extends StudentBaseController
{
    public function index()
    {
        $grades = $this->grades($this->me());

        return view('student.grades', [
            'subjects' => $this->subjects($grades), 'average' => $grades->isEmpty() ? null : round($grades->avg('pct')),
            'work' => $grades->sortByDesc('due')->values(),
        ]);
    }
}

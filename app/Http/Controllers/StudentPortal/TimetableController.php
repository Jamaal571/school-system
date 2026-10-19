<?php

namespace App\Http\Controllers\StudentPortal;

use App\Models\TimetableSlot;

class TimetableController extends StudentBaseController
{
    public function index()
    {
        $class = $this->classOf($this->me());
        $slots = $class ? TimetableSlot::with(['subject', 'teacher'])->where('school_class_id', $class->id)->orderBy('starts_at')->get()->groupBy('day_of_week') : collect();

        return view('student.timetable', compact('class', 'slots'));
    }
}

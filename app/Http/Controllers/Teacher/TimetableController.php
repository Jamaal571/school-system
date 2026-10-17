<?php

namespace App\Http\Controllers\Teacher;

use App\Models\TimetableSlot;

class TimetableController extends TeacherBaseController
{
    public function index()
    {
        $slots = TimetableSlot::with(['schoolClass', 'subject'])->where('staff_member_id', $this->me()->id)
            ->orderBy('starts_at')->get()->groupBy('day_of_week');

        return view('teacher.timetable', compact('slots'));
    }
}

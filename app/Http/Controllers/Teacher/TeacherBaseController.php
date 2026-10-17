<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\StaffMember;
use App\Models\TimetableSlot;

abstract class TeacherBaseController extends Controller
{
    /** The staff record linked to the signed-in teacher. */
    protected function me(): StaffMember
    {
        return StaffMember::where('user_id', auth()->id())->first()
            ?? abort(403, 'Your login is not linked to a staff record yet. Ask the school admin.');
    }

    /** Classes this teacher teaches (timetable) or is homeroom teacher of. */
    protected function myClasses()
    {
        $me = $this->me();
        $ids = TimetableSlot::where('staff_member_id', $me->id)->pluck('school_class_id')
            ->merge(SchoolClass::where('homeroom_teacher_id', $me->id)->pluck('id'))->unique();

        return SchoolClass::whereIn('id', $ids)->orderBy('grade_level')->orderBy('name')->get();
    }
}

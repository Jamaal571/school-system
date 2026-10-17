<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $guarded = [];

    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function homeroomTeacher() { return $this->belongsTo(StaffMember::class, 'homeroom_teacher_id'); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function students() { return $this->belongsToMany(Student::class, 'enrollments'); }
    public function subjects() { return $this->belongsToMany(Subject::class, 'class_subject')->withPivot('staff_member_id'); }
    public function attendances() { return $this->hasMany(Attendance::class); }
}

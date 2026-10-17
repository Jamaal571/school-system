<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    protected $guarded = [];

    public function schoolClass() { return $this->belongsTo(SchoolClass::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(StaffMember::class, 'staff_member_id'); }
}

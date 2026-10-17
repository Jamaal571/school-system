<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $guarded = [];

    protected $casts = ['due_on' => 'date'];

    public function schoolClass() { return $this->belongsTo(SchoolClass::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(StaffMember::class, 'staff_member_id'); }
    public function submissions() { return $this->hasMany(Submission::class); }
}

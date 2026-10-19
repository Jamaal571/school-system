<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffMember extends Model
{
    protected $guarded = [];

    protected $casts = ['hired_on' => 'date', 'monthly_salary' => 'decimal:2'];

    public function user() { return $this->belongsTo(User::class); }
    public function classes() { return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id'); }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    protected $casts = ['date_of_birth'=>'date','admitted_on'=>'date'];

    public function guardians() { return $this->belongsToMany(Guardian::class)->withPivot('relationship', 'is_primary'); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function grades() { return $this->hasMany(Grade::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}

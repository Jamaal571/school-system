<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }
    public function students() { return $this->belongsToMany(Student::class)->withPivot('relationship', 'is_primary'); }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}

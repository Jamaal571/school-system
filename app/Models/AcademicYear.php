<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $guarded = [];

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date', 'is_current' => 'boolean'];

    public static function current()
    {
        return static::where('is_current', true)->first();
    }

    /** The term that is running today, or the first term if none is running. */
    public function currentTerm()
    {
        return $this->terms()->whereDate('starts_on', '<=', today())->orderByDesc('starts_on')->first()
            ?? $this->terms()->orderBy('starts_on')->first();
    }

    public function terms() { return $this->hasMany(Term::class); }
    public function classes() { return $this->hasMany(SchoolClass::class); }
}

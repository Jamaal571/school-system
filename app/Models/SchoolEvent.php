<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolEvent extends Model
{
    protected $guarded = [];

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date'];

    /** Events that have not finished yet, soonest first. */
    public function scopeUpcoming($query)
    {
        return $query->where(fn ($w) => $w->whereDate('starts_on', '>=', today())->orWhereDate('ends_on', '>=', today()))->orderBy('starts_on');
    }

    /** "22–23 Oct" or "15 Oct", with the time when there is one. */
    public function getWhenAttribute(): string
    {
        $s = $this->starts_on;
        $date = $this->ends_on && ! $this->ends_on->isSameDay($s)
            ? ($this->ends_on->isSameMonth($s) ? $s->format('j').'–'.$this->ends_on->format('j M') : $s->format('j M').' – '.$this->ends_on->format('j M'))
            : $s->format('j M');

        return $date.($this->time ? ' · '.$this->time : '');
    }
}

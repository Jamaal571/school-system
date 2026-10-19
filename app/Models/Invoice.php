<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = ['due_on' => 'date', 'amount' => 'decimal:2', 'last_reminded_at' => 'datetime'];

    public function student() { return $this->belongsTo(Student::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function getPaidAttribute()
    {
        return $this->payments->sum('amount');
    }

    /** What is still owed (needs the payments to be loaded). */
    public function getBalanceAttribute(): float
    {
        return max(round((float) $this->amount - (float) $this->payments->sum('amount'), 2), 0);
    }
}

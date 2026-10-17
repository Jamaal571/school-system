<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = ['due_on'=>'date','amount'=>'decimal:2'];

    public function student() { return $this->belongsTo(Student::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function getPaidAttribute()
    {
        return $this->payments->sum('amount');
    }
}

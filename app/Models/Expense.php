<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    protected $casts = ['spent_on' => 'date', 'amount' => 'decimal:2'];

    public function payrollRun() { return $this->belongsTo(PayrollRun::class); }

    /** Expenses between two dates (inclusive). */
    public function scopeBetween($query, $from, $to)
    {
        return $query->whereDate('spent_on', '>=', $from->toDateString())->whereDate('spent_on', '<=', $to->toDateString());
    }
}

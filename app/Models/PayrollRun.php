<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $guarded = [];

    protected $casts = ['month' => 'date', 'total' => 'decimal:2'];
}

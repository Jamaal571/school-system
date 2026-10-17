<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = ['paid_on'=>'date','amount'=>'decimal:2'];

    public function invoice() { return $this->belongsTo(Invoice::class); }
}

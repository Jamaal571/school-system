<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $guarded = [];

    protected $casts = ['starts_on'=>'date','ends_on'=>'date','amount'=>'decimal:2','decided_at'=>'datetime'];

    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [];

    protected $casts = ['read_at' => 'datetime'];

    public function sender() { return $this->belongsTo(User::class, 'from_user_id'); }
    public function recipient() { return $this->belongsTo(User::class, 'to_user_id'); }
    public function student() { return $this->belongsTo(Student::class); }
}

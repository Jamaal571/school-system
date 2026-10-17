<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $guarded = [];

    protected $casts = ['submitted_on' => 'date', 'graded_at' => 'datetime', 'score' => 'decimal:2'];

    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function student() { return $this->belongsTo(Student::class); }
}

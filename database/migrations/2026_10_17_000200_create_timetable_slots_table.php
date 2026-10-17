<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_member_id')->constrained('staff_members')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1 = Monday ... 5 = Friday
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('room')->nullable();
            $table->string('topic')->nullable();
            $table->timestamps();
            $table->index(['staff_member_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
    }
};

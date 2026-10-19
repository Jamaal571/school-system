<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete(); // which child it is about
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['to_user_id', 'read_at']);
        });

        Schema::create('school_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('time')->nullable();      // e.g. 9:00 AM
            $table->string('location')->nullable();
            $table->string('type')->default('event'); // event | meeting | holiday
            $table->string('audience')->default('all'); // all | parents | teachers
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_events');
        Schema::dropIfExists('messages');
    }
};

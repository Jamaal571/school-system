<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // the student's own login
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->text('feedback')->nullable(); // the teacher's comment on the work
        });
    }

    public function down(): void
    {
        Schema::table('submissions', fn (Blueprint $table) => $table->dropColumn('feedback'));
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};

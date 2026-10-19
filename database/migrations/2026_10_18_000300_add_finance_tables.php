<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('category')->default('tuition'); // tuition | transport | activities
            $table->timestamp('last_reminded_at')->nullable();
        });

        Schema::table('staff_members', function (Blueprint $table) {
            $table->decimal('monthly_salary', 10, 2)->default(0);
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->date('month')->unique(); // first day of the month that was paid
            $table->unsignedSmallInteger('staff_count');
            $table->decimal('total', 12, 2);
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // EXP-0001 or PAY-0001
            $table->string('category'); // salaries | supplies | utilities | transport | activities | other
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('spent_on');
            $table->string('status')->default('posted');
            $table->foreignId('payroll_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('spent_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payroll_runs');
        Schema::table('staff_members', fn (Blueprint $table) => $table->dropColumn('monthly_salary'));
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn(['category', 'last_reminded_at']));
    }
};

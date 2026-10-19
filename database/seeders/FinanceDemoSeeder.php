<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PayrollRun;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class FinanceDemoSeeder extends Seeder
{
    /** Adds salaries, expenses, payroll history, and transport/activity fees. Safe to run twice. */
    public function run(): void
    {
        if (Expense::exists()) {
            $this->command?->info('Finance demo data already exists.');
            return;
        }
        $year = AcademicYear::current();
        if (! $year || ! Student::exists()) {
            $this->command?->warn('Run: php artisan migrate:fresh --seed first.');
            return;
        }

        // Salaries
        foreach (StaffMember::all() as $m) {
            $m->update(['monthly_salary' => $m->type === 'teacher' ? fake()->numberBetween(32, 52) * 100 : fake()->numberBetween(18, 26) * 100]);
        }

        // Spread some tuition payments into the previous month so the chart has history
        foreach (Payment::inRandomOrder()->limit(70)->get() as $p) {
            $p->update(['paid_on' => now()->subMonth()->startOfMonth()->addDays(fake()->numberBetween(5, 26))->toDateString()]);
        }

        // Transport and activity invoices for some students
        $n = Invoice::max('id');
        foreach (Student::inRandomOrder()->limit(150)->get() as $student) {
            foreach ([['transport', 60, 'School bus, Term 1'], ['activities', 35, 'Clubs and trips, Term 1']] as [$cat, $amount, $text]) {
                if (fake()->boolean($cat === 'transport' ? 60 : 40)) {
                    $paid = fake()->boolean(70);
                    $invoice = Invoice::create([
                        'student_id' => $student->id, 'academic_year_id' => $year->id,
                        'number' => 'INV-'.str_pad(++$n, 5, '0', STR_PAD_LEFT), 'description' => $text, 'category' => $cat,
                        'amount' => $amount, 'due_on' => now()->subDays(fake()->numberBetween(-10, 20))->toDateString(),
                        'status' => $paid ? 'paid' : 'pending',
                    ]);
                    if ($paid) {
                        Payment::create([
                            'invoice_id' => $invoice->id, 'amount' => $amount, 'method' => fake()->randomElement(['cash', 'bank transfer', 'mobile money']),
                            'paid_on' => now()->subDays(fake()->numberBetween(0, 55))->toDateString(),
                        ]);
                    }
                }
            }
        }

        // Expenses for the last three months. Payroll is posted for the two earlier months only,
        // so you can press "Process payroll" for the current month yourself.
        $payroll = (float) StaffMember::where('status', 'active')->sum('monthly_salary');
        $seq = 0;
        foreach ([2, 1, 0] as $ago) {
            $month = now()->subMonths($ago)->startOfMonth();
            $last = $ago === 0 ? now() : $month->copy()->endOfMonth();

            if ($ago > 0) {
                $run = PayrollRun::create(['month' => $month->toDateString(), 'staff_count' => StaffMember::where('status', 'active')->count(), 'total' => $payroll]);
                Expense::create(['reference' => 'PAY-'.str_pad(++$seq, 4, '0', STR_PAD_LEFT), 'category' => 'salaries', 'description' => 'Staff payroll',
                    'amount' => $payroll, 'spent_on' => $last->toDateString(), 'payroll_run_id' => $run->id]);
            }
            foreach ([['supplies', 'Classroom supplies', 2000, 4500], ['supplies', 'Lab equipment', 1500, 3000], ['utilities', 'Electricity and water', 3500, 5200], ['transport', 'Bus fuel and repairs', 1800, 3200], ['activities', 'Sports day', 600, 1400]] as [$cat, $text, $min, $max]) {
                Expense::create(['reference' => 'EXP-'.str_pad(++$seq, 4, '0', STR_PAD_LEFT), 'category' => $cat, 'description' => $text,
                    'amount' => fake()->numberBetween($min, $max), 'spent_on' => $month->copy()->addDays(fake()->numberBetween(1, max($last->day - 1, 1)))->toDateString()]);
            }
        }
    }
}

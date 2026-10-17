<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Login for the dashboard: admin@northwood.test / password
        $admin = User::forceCreate([
            'name' => 'Dr. James Wilson',
            'email' => 'admin@northwood.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $year = AcademicYear::create([
            'name' => '2026–2027', 'starts_on' => '2026-09-01', 'ends_on' => '2027-06-30', 'is_current' => true,
        ]);
        Term::create([
            'academic_year_id' => $year->id, 'name' => 'Term 1', 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-18',
        ]);

        foreach (['Mathematics', 'English', 'Science', 'History', 'Geography', 'Art', 'Physical Education'] as $i => $name) {
            Subject::create(['name' => $name, 'code' => strtoupper(substr($name, 0, 3)).($i + 1)]);
        }

        // Staff: 16 teachers and 4 support staff
        $teachers = collect();
        foreach (range(1, 20) as $n) {
            $staff = StaffMember::create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'type' => $n <= 16 ? 'teacher' : 'support',
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'hired_on' => fake()->dateTimeBetween('-8 years', '-1 year'),
            ]);
            if ($n <= 16) {
                $teachers->push($staff);
            }
        }

        // 15 classes: Grades 5-9, sections A-C, each with a homeroom teacher
        $classes = collect();
        foreach ([5, 6, 7, 8, 9] as $grade) {
            foreach (['A', 'B', 'C'] as $section) {
                $classes->push(SchoolClass::create([
                    'academic_year_id' => $year->id,
                    'name' => "Grade $grade $section",
                    'grade_level' => $grade,
                    'homeroom_teacher_id' => $teachers[$classes->count()]->id,
                ]));
            }
        }

        // 20 students per class, each with a guardian, enrollment and invoice
        $n = 0;
        foreach ($classes as $class) {
            for ($i = 0; $i < 20; $i++) {
                $n++;
                $last = fake()->lastName();

                $student = Student::create([
                    'admission_no' => 'NW'.str_pad($n, 5, '0', STR_PAD_LEFT),
                    'first_name' => fake()->firstName(),
                    'last_name' => $last,
                    'gender' => fake()->randomElement(['male', 'female']),
                    'date_of_birth' => fake()->dateTimeBetween('-16 years', '-9 years'),
                    'admitted_on' => fake()->dateTimeBetween('-5 years', '-1 month'),
                ]);

                $guardian = Guardian::create([
                    'first_name' => fake()->firstName(),
                    'last_name' => $last,
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->safeEmail(),
                ]);
                $student->guardians()->attach($guardian->id, [
                    'relationship' => fake()->randomElement(['Mother', 'Father', 'Guardian']),
                    'is_primary' => true,
                ]);

                Enrollment::create([
                    'student_id' => $student->id,
                    'school_class_id' => $class->id,
                    'academic_year_id' => $year->id,
                    'enrolled_on' => '2026-09-01',
                ]);

                $invoice = Invoice::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $year->id,
                    'number' => 'INV-'.str_pad($n, 5, '0', STR_PAD_LEFT),
                    'description' => 'Term 1 tuition',
                    'amount' => fake()->randomElement([220, 240, 250, 280, 300]),
                    'due_on' => '2026-10-31',
                    'status' => fake()->randomElement(['paid', 'paid', 'paid', 'pending', 'overdue']),
                ]);

                if ($invoice->status === 'paid') {
                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'amount' => $invoice->amount,
                        'paid_on' => fake()->dateTimeBetween('2026-09-05', '2026-10-14'),
                        'method' => fake()->randomElement(['cash', 'bank transfer', 'mobile money']),
                        'received_by' => $admin->id,
                    ]);
                }
            }
        }

        // Attendance for the last 5 school days (about 96% present)
        $days = collect(range(0, 9))
            ->map(fn ($d) => now()->subDays($d))
            ->filter(fn ($d) => $d->isWeekday())
            ->take(5);
        $pool = array_merge(array_fill(0, 24, 'present'), ['absent', 'late']);

        $rows = [];
        foreach (Enrollment::all() as $enrollment) {
            foreach ($days as $day) {
                $rows[] = [
                    'student_id' => $enrollment->student_id,
                    'school_class_id' => $enrollment->school_class_id,
                    'date' => $day->toDateString(),
                    'status' => fake()->randomElement($pool),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            Attendance::insert($chunk);
        }

        // The approvals shown on the dashboard
        $approvals = [
            ['Science lab supplies', 'budget', 'Finance team', 4800, null, null, 'urgent'],
            ['Annual leave · Sarah Lee', 'leave', 'Human resources', null, '2026-10-19', '2026-10-20', 'normal'],
            ['Library book fund', 'budget', 'Finance team', 2400, null, null, 'normal'],
            ['Grade 9 field trip', 'trip', 'Academic office', 1250, null, null, 'normal'],
        ];
        foreach ($approvals as [$title, $type, $dept, $amount, $from, $to, $priority]) {
            ApprovalRequest::create([
                'title' => $title, 'type' => $type, 'department' => $dept, 'amount' => $amount,
                'starts_on' => $from, 'ends_on' => $to, 'priority' => $priority, 'requested_by' => $admin->id,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\StaffMember;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherDemoSeeder extends Seeder
{
    /** Gives teacher@northwood.test a timetable, assignments and graded work. Safe to run twice. */
    public function run(): void
    {
        $user = User::where('email', 'teacher@northwood.test')->first();
        $me = $user ? StaffMember::where('user_id', $user->id)->first() : null;
        if (! $me) {
            $this->command?->warn('Run DemoUsersSeeder first: php artisan db:seed --class=DemoUsersSeeder');
            return;
        }
        if (TimetableSlot::where('staff_member_id', $me->id)->exists()) {
            $this->command?->info('Teacher demo data already exists.');
            return;
        }

        $me->update(['first_name' => 'Maria', 'last_name' => 'Hassan']);
        $subject = Subject::firstOrCreate(['code' => 'MAT1'], ['name' => 'Mathematics']);
        $classes = SchoolClass::whereIn('name', ['Grade 8 B', 'Grade 9 A', 'Grade 7 B', 'Grade 9 B'])->get()->keyBy('name');
        if ($classes->count() < 4) {
            $this->command?->warn('The demo classes are missing. Run: php artisan migrate:fresh --seed');
            return;
        }

        // The same four periods every school day, Monday to Friday
        $periods = [
            ['08:40:00', '09:30:00', 'Grade 8 B', '204', 'Fractions & ratios'],
            ['10:40:00', '11:30:00', 'Grade 9 A', '204', 'Linear equations'],
            ['12:10:00', '13:00:00', 'Grade 7 B', '206', 'Quadratic functions'],
            ['14:00:00', '14:50:00', 'Grade 9 B', '204', 'Statistics'],
        ];
        foreach (range(1, 5) as $day) {
            foreach ($periods as [$from, $to, $class, $room, $topic]) {
                TimetableSlot::create([
                    'school_class_id' => $classes[$class]->id, 'subject_id' => $subject->id, 'staff_member_id' => $me->id,
                    'day_of_week' => $day, 'starts_at' => $from, 'ends_at' => $to, 'room' => $room, 'topic' => $topic,
                ]);
            }
        }

        // [title, class, due in N days, max score, how many submissions are still waiting to be graded]
        $assignments = [
            ['Fractions practice', 'Grade 8 B', -3, 20, 4],
            ['Linear equations quiz', 'Grade 9 A', -2, 30, 5],
            ['Quadratic functions', 'Grade 7 B', -4, 25, 3],
            ['Ratios worksheet', 'Grade 9 B', -8, 20, 0],
            ['Linear equations worksheet', 'Grade 9 A', 1, 20, 0],
        ];
        $skill = []; // each student keeps a similar level across assignments
        foreach ($assignments as [$title, $className, $offset, $max, $waiting]) {
            $assignment = Assignment::create([
                'school_class_id' => $classes[$className]->id, 'subject_id' => $subject->id, 'staff_member_id' => $me->id,
                'title' => $title, 'due_on' => now()->addDays($offset)->toDateString(), 'max_score' => $max,
            ]);
            if ($offset > 0) {
                continue; // not due yet, nothing submitted
            }
            foreach ($classes[$className]->students()->get()->shuffle() as $student) {
                if (fake()->boolean(12)) {
                    continue; // work not handed in
                }
                $skill[$student->id] ??= fake()->numberBetween(42, 98);
                $percent = max(15, min(100, $skill[$student->id] + fake()->numberBetween(-8, 8)));
                $isWaiting = $waiting > 0;
                if ($isWaiting) {
                    $waiting--;
                }
                Submission::create([
                    'assignment_id' => $assignment->id, 'student_id' => $student->id,
                    'submitted_on' => now()->addDays($offset - fake()->numberBetween(0, 2))->toDateString(),
                    'score' => $isWaiting ? null : round($percent / 100 * $max, 1),
                    'graded_at' => $isWaiting ? null : now(),
                ]);
            }
        }
    }
}

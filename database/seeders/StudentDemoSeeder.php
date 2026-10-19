<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Message;
use App\Models\SchoolClass;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentDemoSeeder extends Seeder
{
    /** Links student@northwood.test to Maya Chen and fills her day: timetable, deadlines, feedback. */
    public function run(): void
    {
        $user = User::where('email', 'student@northwood.test')->first();
        $maya = Student::where('first_name', 'Maya')->where('last_name', 'Chen')->first();
        $teacherUser = User::where('email', 'teacher@northwood.test')->first();
        $maths = $teacherUser ? StaffMember::where('user_id', $teacherUser->id)->first() : null;
        $class = SchoolClass::where('name', 'Grade 9 A')->first();
        if (! $user || ! $maya || ! $maths || ! $class) {
            $this->command?->warn('Run DemoUsersSeeder, TeacherDemoSeeder and ParentDemoSeeder first.');
            return;
        }
        if ($maya->user_id === $user->id) {
            $this->command?->info('Student demo data already exists.');
            return;
        }

        $user->update(['name' => 'Maya Chen']);
        $maya->update(['user_id' => $user->id]);

        // A full day: the maths slot already exists at 10:40, so add the other four subjects
        $teachers = StaffMember::where('type', 'teacher')->where('id', '!=', $maths->id)->inRandomOrder()->take(4)->get();
        $day = [
            ['Science', '08:40:00', '09:25:00', 'Lab 2', 'Cell structure'],
            ['History', '09:35:00', '10:20:00', 'Room 108', 'Industrial revolution'],
            ['English', '12:10:00', '12:55:00', 'Room 112', 'Poetry analysis'],
            ['Physical Education', '14:00:00', '14:45:00', 'Sports field', 'Team games'],
        ];
        foreach ($day as $i => [$subjectName, $from, $to, $room, $topic]) {
            $subject = Subject::firstOrCreate(['name' => $subjectName], ['code' => strtoupper(substr($subjectName, 0, 3)).rand(10, 99)]);
            foreach (range(1, 5) as $dow) {
                TimetableSlot::create(['school_class_id' => $class->id, 'subject_id' => $subject->id, 'staff_member_id' => $teachers[$i % $teachers->count()]->id,
                    'day_of_week' => $dow, 'starts_at' => $from, 'ends_at' => $to, 'room' => $room, 'topic' => $topic]);
            }
        }

        // Two more deadlines
        foreach ([['Cell structure lab report', 'Science', 3, 25], ['Poetry analysis', 'English', 4, 20]] as [$title, $subjectName, $days, $max]) {
            $slot = TimetableSlot::where('school_class_id', $class->id)->whereHas('subject', fn ($q) => $q->where('name', $subjectName))->first();
            Assignment::create(['school_class_id' => $class->id, 'subject_id' => $slot->subject_id, 'staff_member_id' => $slot->staff_member_id,
                'title' => $title, 'due_on' => now()->addDays($days)->toDateString(), 'max_score' => $max]);
        }

        // Teacher feedback on her latest graded maths work
        $latest = Submission::whereHas('assignment', fn ($q) => $q->where('staff_member_id', $maths->id)->where('school_class_id', $class->id))
            ->where('student_id', $maya->id)->whereNotNull('score')->latest('id')->first();
        $latest?->update(['feedback' => 'Excellent reasoning, Maya. Remember to show each step when solving.']);

        Message::create(['from_user_id' => $teacherUser->id, 'to_user_id' => $user->id,
            'body' => 'Revision notes for tomorrow’s worksheet: focus on solving for x one step at a time.', 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)]);
        Announcement::create(['title' => 'Science fair registration', 'body' => 'Project titles are due by Monday. See your science teacher if you need help choosing.',
            'audience' => 'students', 'user_id' => User::where('role', 'admin')->value('id')]);
    }
}

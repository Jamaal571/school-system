<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\SchoolEvent;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class ParentDemoSeeder extends Seeder
{
    /** Gives parent@northwood.test two children (Maya and Leo) with grades, attendance, fees and messages. */
    public function run(): void
    {
        $user = User::where('email', 'parent@northwood.test')->first();
        $guardian = $user ? Guardian::where('user_id', $user->id)->first() : null;
        $teacher = User::where('email', 'teacher@northwood.test')->first();
        $teacherStaff = $teacher ? StaffMember::where('user_id', $teacher->id)->first() : null;
        if (! $guardian || ! $teacherStaff) {
            $this->command?->warn('Run DemoUsersSeeder and TeacherDemoSeeder first.');
            return;
        }
        if ($guardian->students()->where('first_name', 'Maya')->exists()) {
            $this->command?->info('Parent demo data already exists.');
            return;
        }

        $year = AcademicYear::current();
        $term = $year->currentTerm();
        $user->update(['name' => 'Daniel Chen']);
        $guardian->update(['first_name' => 'Daniel', 'last_name' => 'Chen']);
        $guardian->students()->detach(); // start with just the two demo children

        // Two children
        $maya = SchoolClass::where('name', 'Grade 9 A')->first()?->students()->orderBy('students.id')->first();
        $leo = SchoolClass::where('name', 'Grade 7 B')->first()?->students()->orderBy('students.id')->first();
        if (! $maya || ! $leo) {
            $this->command?->warn('Run TeacherDemoSeeder first (it needs the demo classes).');
            return;
        }
        $maya->update(['first_name' => 'Maya', 'last_name' => 'Chen', 'gender' => 'female', 'status' => 'active']);
        $leo->update(['first_name' => 'Leo', 'last_name' => 'Chen', 'gender' => 'male', 'status' => 'active']);
        foreach ([$maya, $leo] as $child) {
            $child->guardians()->syncWithoutDetaching([$guardian->id => ['relationship' => 'Father', 'is_primary' => true]]);
        }

        // Grades in four subjects for Maya (high achiever); Leo keeps the maths work from the teacher demo
        $mayaClass = SchoolClass::where('name', 'Grade 9 A')->first();
        $teachers = StaffMember::where('type', 'teacher')->where('id', '!=', $teacherStaff->id)->take(3)->get();
        foreach (['Science' => [['Cell structure', 25], ['Energy and motion', 30]], 'English' => [['Creative writing', 20], ['Book review', 20]], 'History' => [['Industrial revolution', 20], ['Source analysis', 20]]] as $name => $works) {
            $subject = Subject::firstOrCreate(['name' => $name], ['code' => strtoupper(substr($name, 0, 3)).rand(10, 99)]);
            $staff = $teachers->shift() ?? $teacherStaff;
            foreach ($works as $i => [$title, $max]) {
                $a = Assignment::create(['school_class_id' => $mayaClass->id, 'subject_id' => $subject->id, 'staff_member_id' => $staff->id,
                    'title' => $title, 'due_on' => now()->subDays(20 - $i * 7)->toDateString(), 'max_score' => $max]);
                Submission::create(['assignment_id' => $a->id, 'student_id' => $maya->id, 'submitted_on' => $a->due_on->toDateString(),
                    'score' => round($max * fake()->numberBetween(90, 97) / 100, 1), 'graded_at' => now()]);
            }
        }
        // Make sure both children have graded maths work
        foreach ([$maya, $leo] as $child) {
            foreach (Assignment::where('staff_member_id', $teacherStaff->id)->whereIn('school_class_id', $child->enrollments()->pluck('school_class_id'))->whereDate('due_on', '<', today())->get() as $a) {
                Submission::updateOrCreate(['assignment_id' => $a->id, 'student_id' => $child->id], [
                    'submitted_on' => $a->due_on->toDateString(), 'score' => round($a->max_score * fake()->numberBetween(86, 96) / 100, 1), 'graded_at' => now()]);
            }
        }

        // Attendance since the term began: nearly always present, one excused absence for Maya
        $from = ($term?->starts_on ?? now()->subDays(45))->copy();
        $excused = false;
        foreach ([$maya, $leo] as $child) {
            for ($d = $from->copy(); $d->lte(now()); $d->addDay()) {
                if ($d->isWeekend()) {
                    continue;
                }
                $status = 'present';
                if ($child->id === $maya->id && ! $excused && $d->diffInDays(now()) > 12 && $d->diffInDays(now()) < 20) {
                    $status = 'excused';
                    $excused = true;
                }
                Attendance::updateOrCreate(['student_id' => $child->id, 'date' => $d->toDateString()], [
                    'school_class_id' => $child->enrollments()->value('school_class_id'), 'status' => $status]);
            }
        }

        // Fees: Maya has an overdue October instalment with $750 already paid
        $n = Invoice::max('id') + 1;
        $invoice = Invoice::create(['student_id' => $maya->id, 'academic_year_id' => $year->id, 'number' => 'INV-'.str_pad($n, 5, '0', STR_PAD_LEFT),
            'description' => 'October tuition instalment', 'category' => 'tuition', 'amount' => 1200, 'due_on' => now()->subDays(5)->toDateString(), 'status' => 'overdue']);
        Payment::create(['invoice_id' => $invoice->id, 'amount' => 450, 'method' => 'cash', 'paid_on' => now()->subDays(35)->toDateString()]);
        Payment::create(['invoice_id' => $invoice->id, 'amount' => 300, 'method' => 'bank transfer', 'paid_on' => now()->subDays(6)->toDateString()]);

        // Messages, events and a notice
        Message::create(['from_user_id' => $user->id, 'to_user_id' => $teacher->id, 'student_id' => $maya->id,
            'body' => 'Could we discuss Maya’s progress at the conference?', 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3), 'read_at' => now()->subDays(3)]);
        Message::create(['from_user_id' => $teacher->id, 'to_user_id' => $user->id, 'student_id' => $maya->id,
            'body' => 'Maya is making excellent progress. Her reasoning in the latest quiz was particularly strong.', 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)]);

        if (! SchoolEvent::exists()) {
            SchoolEvent::create(['title' => 'Parent–teacher conferences', 'description' => 'Meet your child’s subject teachers.', 'starts_on' => now()->addDays(4)->toDateString(), 'ends_on' => now()->addDays(5)->toDateString(), 'type' => 'meeting', 'audience' => 'all']);
            SchoolEvent::create(['title' => 'School science fair', 'description' => 'Families welcome.', 'starts_on' => now()->addDays(5)->toDateString(), 'time' => '9:00 AM', 'location' => 'Main hall', 'type' => 'event', 'audience' => 'all']);
            SchoolEvent::create(['title' => 'Term 1 progress reports available', 'starts_on' => now()->addDays(12)->toDateString(), 'type' => 'event', 'audience' => 'parents']);
        }
        Announcement::create(['title' => 'School notice', 'body' => 'Term 1 progress reports will be available on '.now()->addDays(12)->format('j F').'. You’ll receive a notification here.', 'audience' => 'parents', 'user_id' => User::where('role', 'admin')->value('id')]);
    }
}

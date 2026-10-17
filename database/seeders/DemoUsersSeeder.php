<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /** Adds one demo login per role without touching your other data. Password: "password". */
    public function run(): void
    {
        $make = fn (string $name, string $email, string $role) => User::where('email', $email)->first()
            ?? User::forceCreate([
                'name' => $name, 'email' => $email, 'password' => Hash::make('password'),
                'role' => $role, 'email_verified_at' => now(),
            ]);

        $make('Dr. James Wilson', 'admin@northwood.test', 'admin');

        $teacher = $make('Maria Hassan', 'teacher@northwood.test', 'teacher');
        StaffMember::where('type', 'teacher')->whereNull('user_id')->first()?->update(['user_id' => $teacher->id]);

        $make('Finance Officer', 'finance@northwood.test', 'finance');

        $parent = $make('Parent Demo', 'parent@northwood.test', 'parent');
        Guardian::whereNull('user_id')->first()?->update(['user_id' => $parent->id]);

        $make('Student Demo', 'student@northwood.test', 'student');
    }
}

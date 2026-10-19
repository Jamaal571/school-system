<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public const ROLES = ['admin' => 'Admin', 'teacher' => 'Teacher', 'finance' => 'Finance', 'parent' => 'Parent', 'student' => 'Student'];

    public function create()
    {
        return view('admin.users.create', ['roles' => self::ROLES, 'users' => User::latest('id')->limit(10)->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120|unique:users,email',
            'role' => 'required|in:'.implode(',', array_keys(self::ROLES)),
            'password' => 'required|string|min:8',
            'link' => 'nullable|string|max:120',
        ]);

        // Teachers, parents and students must be tied to a real record, or their dashboard stays empty.
        $target = null;
        if (in_array($data['role'], ['teacher', 'parent', 'student'], true)) {
            $link = trim($data['link'] ?? '');
            $target = match ($data['role']) {
                'teacher' => StaffMember::where('email', $link)->where('type', 'teacher')->whereNull('user_id')->first(),
                'student' => Student::where('admission_no', $link)->whereNull('user_id')->first(),
                default => Student::where('admission_no', $link)->first(),
            };
            if (! $target) {
                return back()->withInput()->withErrors(['link' => $link === ''
                    ? 'Enter the person to link this login to (see the hint under the field).'
                    : "Nothing matches “{$link}”, or it already has a login."]);
            }
        }

        DB::transaction(function () use ($data, $target) {
            $user = User::forceCreate([
                'name' => $data['name'], 'email' => $data['email'], 'role' => $data['role'],
                'password' => Hash::make($data['password']), 'email_verified_at' => now(),
            ]);

            if (in_array($data['role'], ['teacher', 'student'], true)) {
                $target->update(['user_id' => $user->id]);
            } elseif ($data['role'] === 'parent') {
                $guardian = $target->guardians()->whereNull('guardians.user_id')->first();
                if ($guardian) {
                    $guardian->update(['user_id' => $user->id]);
                } else {
                    [$first, $last] = array_pad(explode(' ', $data['name'], 2), 2, '');
                    $guardian = Guardian::create(['first_name' => $first, 'last_name' => $last ?: $target->last_name, 'email' => $data['email'], 'user_id' => $user->id]);
                    $target->guardians()->attach($guardian->id, ['relationship' => 'Guardian', 'is_primary' => false]);
                }
            }
        });

        return redirect()->route('admin.users.create')->with('status', "Account created for {$data['name']} ({$data['role']}).");
    }
}

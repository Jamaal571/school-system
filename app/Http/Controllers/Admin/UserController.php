<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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
        ]);

        User::forceCreate([
            'name' => $data['name'], 'email' => $data['email'], 'role' => $data['role'],
            'password' => Hash::make($data['password']), 'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.create')->with('status', "Account created for {$data['name']} ({$data['role']}).");
    }
}

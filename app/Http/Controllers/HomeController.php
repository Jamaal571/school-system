<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** After login, send every user to the dashboard for their own role. */
    public function __invoke(Request $request)
    {
        return redirect()->route(match ($request->user()->role) {
            'admin' => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'finance' => 'finance.dashboard',
            'student' => 'student.dashboard',
            default => 'parent.dashboard',
        });
    }
}

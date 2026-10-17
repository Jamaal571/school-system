<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function index()
    {
        return view('admin.communications.index', ['announcements' => Announcement::with('user')->latest()->paginate(10)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:2000',
            'audience' => 'required|in:all,teachers,parents,students',
        ]);

        Announcement::create($data + ['user_id' => auth()->id()]);

        return redirect()->route('admin.communications')->with('status', 'Announcement posted.');
    }
}

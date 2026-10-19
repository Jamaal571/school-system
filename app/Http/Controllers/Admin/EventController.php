<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        return view('admin.events.index', ['upcoming' => SchoolEvent::upcoming()->get(), 'past' => SchoolEvent::whereDate('starts_on', '<', today())->where(fn ($q) => $q->whereNull('ends_on')->orWhereDate('ends_on', '<', today()))->latest('starts_on')->limit(10)->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120', 'description' => 'nullable|string|max:500',
            'starts_on' => 'required|date', 'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'time' => 'nullable|string|max:20', 'location' => 'nullable|string|max:80',
            'type' => 'required|in:event,meeting,holiday', 'audience' => 'required|in:all,parents,teachers',
        ]);
        SchoolEvent::create($data);

        return redirect()->route('admin.events')->with('status', "“{$data['title']}” added to the calendar.");
    }

    public function destroy(SchoolEvent $event)
    {
        $event->delete();

        return redirect()->route('admin.events')->with('status', 'Event removed.');
    }
}

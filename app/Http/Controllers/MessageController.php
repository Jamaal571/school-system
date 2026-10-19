<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Message;
use App\Models\SchoolClass;
use App\Models\StaffMember;
use App\Models\TimetableSlot;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Http\Request;

/** Parents write to their children's teachers; teachers write to parents of their students. */
class MessageController extends Controller
{
    public function index()
    {
        $me = auth()->id();
        $threads = Message::where('from_user_id', $me)->orWhere('to_user_id', $me)->latest('id')->get()
            ->groupBy(fn ($m) => $m->from_user_id === $me ? $m->to_user_id : $m->from_user_id)
            ->map(fn ($g) => ['last' => $g->first(), 'unread' => $g->where('to_user_id', $me)->whereNull('read_at')->count()]);

        return view('messages.index', ['role' => auth()->user()->role, 'threads' => $threads, 'people' => User::whereIn('id', $threads->keys())->get()->keyBy('id')]);
    }

    public function create(Request $request)
    {
        return view('messages.create', ['role' => auth()->user()->role, 'recipients' => $this->recipients(), 'to' => (int) $request->query('to')]);
    }

    public function show(User $user)
    {
        $me = auth()->id();
        $messages = Message::where(fn ($q) => $q->where('from_user_id', $me)->where('to_user_id', $user->id))
            ->orWhere(fn ($q) => $q->where('from_user_id', $user->id)->where('to_user_id', $me))->orderBy('id')->get();
        abort_if($messages->isEmpty() && ! $this->recipients()->contains('id', $user->id), 403);

        Message::where('from_user_id', $user->id)->where('to_user_id', $me)->whereNull('read_at')->update(['read_at' => now()]);

        return view('messages.show', ['role' => auth()->user()->role, 'other' => $user, 'messages' => $messages]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['to_user_id' => 'required|integer|exists:users,id', 'body' => 'required|string|max:2000']);
        $allowed = $this->recipients()->contains('id', (int) $data['to_user_id'])
            || Message::where('from_user_id', $data['to_user_id'])->where('to_user_id', auth()->id())->exists();
        abort_unless($allowed, 403, 'You cannot message this person.');

        Message::create($data + ['from_user_id' => auth()->id(), 'student_id' => auth()->user()->role === 'parent' ? session('parent_child') : null]);

        return redirect()->route(auth()->user()->role.'.messages.show', $data['to_user_id'])->with('status', 'Message sent.');
    }

    /** People the signed-in user is allowed to start a conversation with. */
    private function recipients()
    {
        $user = auth()->user();

        if ($user->role === 'parent') {
            $children = Guardian::where('user_id', $user->id)->first()?->students()->pluck('students.id') ?? collect();
            $classIds = \App\Models\Enrollment::whereIn('student_id', $children)->pluck('school_class_id');
            $staffIds = TimetableSlot::whereIn('school_class_id', $classIds)->pluck('staff_member_id')
                ->merge(SchoolClass::whereIn('id', $classIds)->pluck('homeroom_teacher_id'))
                ->merge(Assignment::whereIn('school_class_id', $classIds)->pluck('staff_member_id'))->filter()->unique();

            return User::whereIn('id', StaffMember::whereIn('id', $staffIds)->whereNotNull('user_id')->pluck('user_id'))->orderBy('name')->get();
        }

        if ($user->role === 'teacher') {
            $me = StaffMember::where('user_id', $user->id)->first();
            $classIds = TimetableSlot::where('staff_member_id', $me?->id)->pluck('school_class_id')->merge(SchoolClass::where('homeroom_teacher_id', $me?->id)->pluck('id'))->unique();
            $students = \App\Models\Enrollment::whereIn('school_class_id', $classIds)->pluck('student_id');

            $ids = Guardian::whereHas('students', fn ($q) => $q->whereIn('students.id', $students))->whereNotNull('user_id')->pluck('user_id')
                ->merge(\App\Models\Student::whereIn('id', $students)->whereNotNull('user_id')->pluck('user_id'));

            return User::whereIn('id', $ids)->orderBy('name')->get();
        }

        if ($user->role === 'student') {
            $me = \App\Models\Student::where('user_id', $user->id)->first();
            $classIds = \App\Models\Enrollment::where('student_id', $me?->id)->pluck('school_class_id');
            $staffIds = TimetableSlot::whereIn('school_class_id', $classIds)->pluck('staff_member_id')
                ->merge(SchoolClass::whereIn('id', $classIds)->pluck('homeroom_teacher_id'))
                ->merge(Assignment::whereIn('school_class_id', $classIds)->pluck('staff_member_id'))->filter()->unique();

            return User::whereIn('id', StaffMember::whereIn('id', $staffIds)->whereNotNull('user_id')->pluck('user_id'))->orderBy('name')->get();
        }

        return collect();
    }
}

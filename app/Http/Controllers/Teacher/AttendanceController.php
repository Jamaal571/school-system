<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends TeacherBaseController
{
    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d', 'class' => 'nullable|integer']);

        $classes = $this->myClasses();
        $class = $classes->firstWhere('id', (int) $request->class) ?? $classes->first();
        $date = $request->date ? Carbon::parse($request->date) : (now()->isWeekend() ? now()->previousWeekday() : now());

        $rows = $class
            ? Enrollment::with('student')->where('school_class_id', $class->id)->get()->sortBy(fn ($e) => $e->student->first_name)->values()
            : collect();
        $existing = $class
            ? Attendance::where('school_class_id', $class->id)->whereDate('date', $date->toDateString())->pluck('status', 'student_id')
            : collect();

        return view('teacher.attendance', compact('classes', 'class', 'date', 'rows', 'existing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class' => 'required|exists:school_classes,id',
            'date' => 'required|date_format:Y-m-d|before_or_equal:today',
            'status' => 'required|array',
            'status.*' => 'in:present,late,absent,excused',
        ]);
        abort_unless($this->myClasses()->contains('id', (int) $data['class']), 403, 'This is not one of your classes.');

        $enrolled = Enrollment::where('school_class_id', $data['class'])->pluck('student_id')->map(fn ($id) => (int) $id)->all();
        foreach ($data['status'] as $studentId => $status) {
            if (in_array((int) $studentId, $enrolled, true)) {
                Attendance::updateOrCreate(
                    ['student_id' => $studentId, 'date' => $data['date']],
                    ['school_class_id' => $data['class'], 'status' => $status, 'marked_by' => auth()->id()]
                );
            }
        }

        return redirect()->route('teacher.attendance', ['class' => $data['class'], 'date' => $data['date']])
            ->with('status', 'Attendance saved for '.SchoolClass::find($data['class'])->name.' on '.Carbon::parse($data['date'])->format('j M').'.');
    }
}

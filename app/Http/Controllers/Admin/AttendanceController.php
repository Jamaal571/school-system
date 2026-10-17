<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d', 'class' => 'nullable|integer']);

        $classes = SchoolClass::where('academic_year_id', AcademicYear::current()?->id)
            ->orderBy('grade_level')->orderBy('name')->get();
        $class = $classes->firstWhere('id', (int) $request->class) ?? $classes->first();

        // Default to today, or the last weekday when today is a weekend.
        $date = $request->date ? Carbon::parse($request->date) : (now()->isWeekend() ? now()->previousWeekday() : now());

        $rows = $class
            ? Enrollment::with('student')->where('school_class_id', $class->id)->get()->sortBy(fn ($e) => $e->student->first_name)->values()
            : collect();
        $existing = $class
            ? Attendance::where('school_class_id', $class->id)->whereDate('date', $date->toDateString())->pluck('status', 'student_id')
            : collect();

        return view('admin.attendance.index', compact('classes', 'class', 'date', 'rows', 'existing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class' => 'required|exists:school_classes,id',
            'date' => 'required|date_format:Y-m-d|before_or_equal:today',
            'status' => 'required|array',
            'status.*' => 'in:present,late,absent,excused',
        ]);

        $enrolled = Enrollment::where('school_class_id', $data['class'])->pluck('student_id')->map(fn ($id) => (int) $id)->all();

        foreach ($data['status'] as $studentId => $status) {
            if (! in_array((int) $studentId, $enrolled, true)) {
                continue; // ignore students who are not in this class
            }
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $data['date']],
                ['school_class_id' => $data['class'], 'status' => $status, 'marked_by' => auth()->id()]
            );
        }

        $name = SchoolClass::find($data['class'])->name;

        return redirect()->route('admin.attendance', ['class' => $data['class'], 'date' => $data['date']])
            ->with('status', "Attendance saved for $name on ".Carbon::parse($data['date'])->format('j M').'.');
    }
}

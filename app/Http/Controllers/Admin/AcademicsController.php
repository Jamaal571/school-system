<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\StaffMember;
use App\Models\Subject;
use Illuminate\Http\Request;

class AcademicsController extends Controller
{
    public function index()
    {
        return view('admin.academics.index', [
            'classes' => SchoolClass::with('homeroomTeacher')->withCount('enrollments')
                ->where('academic_year_id', AcademicYear::current()?->id)->orderBy('grade_level')->orderBy('name')->get(),
            'subjects' => Subject::withCount('classes')->orderBy('name')->get(),
            'teachers' => StaffMember::where('type', 'teacher')->where('status', 'active')->orderBy('first_name')->get(),
        ]);
    }

    public function storeClass(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60',
            'grade_level' => 'required|integer|min:1|max:12',
            'capacity' => 'nullable|integer|min:1|max:200',
            'homeroom_teacher_id' => 'nullable|exists:staff_members,id',
        ]);
        $year = AcademicYear::current();
        abort_if(! $year, 422, 'Set up the academic year in Settings first.');

        SchoolClass::create($data + ['academic_year_id' => $year->id, 'capacity' => $data['capacity'] ?? 35]);

        return redirect()->route('admin.academics')->with('status', "Class {$data['name']} added.");
    }

    public function storeSubject(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80', 'code' => 'required|string|max:12|unique:subjects,code']);
        Subject::create($data);

        return redirect()->route('admin.academics')->with('status', "Subject {$data['name']} added.");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $yearId = AcademicYear::current()?->id;

        $students = Student::query()
            ->with(['enrollments' => fn ($q) => $q->where('academic_year_id', $yearId)->with('schoolClass')])
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w
                ->where('first_name', 'like', "%$t%")
                ->orWhere('last_name', 'like', "%$t%")
                ->orWhere('admission_no', 'like', "%$t%")))
            ->when($request->class, fn ($q, $c) => $q->whereHas('enrollments', fn ($e) => $e->where('school_class_id', $c)))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('first_name')->orderBy('last_name')
            ->paginate(15)->withQueryString();

        return view('admin.students.index', ['students' => $students, 'classes' => $this->classes()]);
    }

    public function create()
    {
        return view('admin.students.form', $this->formData(new Student));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($data) {
            $student = Student::create([
                'admission_no' => 'NW'.str_pad(Student::max('id') + 1, 5, '0', STR_PAD_LEFT),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'admitted_on' => now()->toDateString(),
                'status' => $data['status'] ?? 'active',
            ]);
            $this->saveRelations($student, $data);
        });

        return redirect()->route('admin.students.index')->with('status', 'Student added.');
    }

    public function show(Student $student)
    {
        $student->load(['guardians', 'enrollments.schoolClass', 'invoices.payments']);

        return view('admin.students.show', [
            'student' => $student,
            'recent' => $student->attendances()->latest('date')->limit(8)->get(),
            'summary' => $student->attendances()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function edit(Student $student)
    {
        return view('admin.students.form', $this->formData($student));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($student, $data) {
            $student->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'status' => $data['status'] ?? $student->status,
            ]);
            $this->saveRelations($student, $data);
        });

        return redirect()->route('admin.students.show', $student)->with('status', 'Student updated.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('status', 'Student deleted.');
    }

    private function rules(): array
    {
        return [
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'status' => 'nullable|in:active,graduated,withdrawn',
            'school_class_id' => 'required|exists:school_classes,id',
            'guardian_first_name' => 'nullable|string|max:80',
            'guardian_last_name' => 'nullable|string|max:80',
            'guardian_phone' => 'nullable|string|max:40',
            'guardian_relationship' => 'nullable|in:Mother,Father,Guardian',
        ];
    }

    private function classes()
    {
        return SchoolClass::where('academic_year_id', AcademicYear::current()?->id)->orderBy('grade_level')->orderBy('name')->get();
    }

    private function formData(Student $student): array
    {
        $year = AcademicYear::current();
        $guardian = $student->exists ? $student->guardians()->first() : null;

        return [
            'student' => $student,
            'classes' => $this->classes(),
            'classId' => $student->exists ? $student->enrollments()->where('academic_year_id', $year?->id)->value('school_class_id') : null,
            'guardian' => $guardian,
            'relationship' => $guardian?->pivot->relationship,
        ];
    }

    private function saveRelations(Student $student, array $data): void
    {
        $year = AcademicYear::current();
        abort_if(! $year, 422, 'Create an academic year first.');

        Enrollment::updateOrCreate(
            ['student_id' => $student->id, 'academic_year_id' => $year->id],
            ['school_class_id' => $data['school_class_id'], 'enrolled_on' => now()->toDateString()]
        );

        if (filled($data['guardian_first_name'] ?? null)) {
            $guardian = $student->guardians()->first() ?? new Guardian;
            $guardian->fill([
                'first_name' => $data['guardian_first_name'],
                'last_name' => $data['guardian_last_name'] ?? $student->last_name,
                'phone' => $data['guardian_phone'] ?? null,
            ])->save();

            $student->guardians()->syncWithoutDetaching([
                $guardian->id => ['relationship' => $data['guardian_relationship'] ?? null, 'is_primary' => true],
            ]);
        }
    }
}

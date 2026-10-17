<?php

use App\Http\Controllers\Admin\AcademicsController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ClassController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\GradebookController;
use App\Http\Controllers\Teacher\TimetableController;
use App\Models\Announcement;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Breeze sends users here after login; we forward them to their own dashboard.
Route::get('/dashboard', HomeController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ---- Admin (Head of school) ----
Route::middleware(['auth', EnsureRole::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('students', StudentController::class);
    Route::resource('staff', StaffController::class)->except('show')->parameters(['staff' => 'staffMember']);

    Route::get('academics', [AcademicsController::class, 'index'])->name('academics');
    Route::post('academics/classes', [AcademicsController::class, 'storeClass'])->name('academics.classes.store');
    Route::post('academics/subjects', [AcademicsController::class, 'storeSubject'])->name('academics.subjects.store');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('finance', [FinanceController::class, 'index'])->name('finance');
    Route::post('finance/invoices', [FinanceController::class, 'storeInvoice'])->name('finance.invoices.store');
    Route::post('finance/invoices/{invoice}/payments', [FinanceController::class, 'storePayment'])->name('finance.payments.store');

    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals');
    Route::post('approvals/{approval}/{decision}', [ApprovalController::class, 'decide'])
        ->whereIn('decision', ['approve', 'reject'])->name('approvals.decide');

    Route::get('communications', [CommunicationController::class, 'index'])->name('communications');
    Route::post('communications', [CommunicationController::class, 'store'])->name('communications.store');

    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');

    Route::get('reports', [ReportController::class, 'index'])->name('reports');
    Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::view('help', 'admin.help')->name('help');
});

// ---- Teacher ----
Route::middleware(['auth', EnsureRole::class.':teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');

    Route::get('classes', [ClassController::class, 'index'])->name('classes');
    Route::get('classes/{class}', [ClassController::class, 'show'])->name('classes.show');

    Route::get('attendance', [TeacherAttendanceController::class, 'index'])->name('attendance');
    Route::post('attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');

    Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments');
    Route::get('assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('assignments/{assignment}/grade', [AssignmentController::class, 'grade'])->name('assignments.grade');
    Route::post('assignments/{assignment}/grade', [AssignmentController::class, 'saveGrades'])->name('assignments.grades');

    Route::get('gradebook', [GradebookController::class, 'index'])->name('gradebook');
    Route::get('timetable', [TimetableController::class, 'index'])->name('timetable');

    Route::get('communications', fn () => view('teacher.communications', [
        'announcements' => Announcement::with('user')->whereIn('audience', ['all', 'teachers'])->latest()->paginate(10),
    ]))->name('communications');
    Route::view('help', 'teacher.help')->name('help');
});

// ---- Other roles: placeholders until their dashboards are built ----
foreach (['finance' => 'Finance', 'parent' => 'Parent', 'student' => 'Student'] as $role => $label) {
    Route::middleware(['auth', EnsureRole::class.':'.$role])->prefix($role)->name($role.'.')->group(function () use ($label) {
        Route::get('/', fn () => view('dashboards.coming-soon', ['role' => $label]))->name('dashboard');
    });
}

require __DIR__.'/auth.php';

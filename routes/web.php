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
use App\Http\Controllers\Finance\BudgetController;
use App\Http\Controllers\Finance\CollectionController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\OverviewController;
use App\Http\Controllers\Finance\PayrollController;
use App\Http\Controllers\Finance\ReportController as FinanceReportController;
use App\Http\Controllers\Finance\TransactionController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ParentPortal\AttendanceController as ParentAttendanceController;
use App\Http\Controllers\ParentPortal\CalendarController;
use App\Http\Controllers\ParentPortal\ChildrenController;
use App\Http\Controllers\ParentPortal\DashboardController as ParentDashboardController;
use App\Http\Controllers\ParentPortal\FeesController;
use App\Http\Controllers\ParentPortal\ProgressController;
use App\Http\Controllers\StudentPortal\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\StudentPortal\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\StudentPortal\CourseController;
use App\Http\Controllers\StudentPortal\DashboardController as StudentDashboardController;
use App\Http\Controllers\StudentPortal\GradesController;
use App\Http\Controllers\StudentPortal\TimetableController as StudentTimetableController;
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

    Route::get('events', [EventController::class, 'index'])->name('events');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
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

    Route::get('messages', [MessageController::class, 'index'])->name('messages');
    Route::get('messages/new', [MessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{user}', [MessageController::class, 'show'])->name('messages.show');

    Route::get('communications', fn () => view('teacher.communications', [
        'announcements' => Announcement::with('user')->whereIn('audience', ['all', 'teachers'])->latest()->paginate(10),
    ]))->name('communications');
    Route::view('help', 'teacher.help')->name('help');
});

// ---- Finance ----
Route::middleware(['auth', EnsureRole::class.':finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [OverviewController::class, 'index'])->name('dashboard');

    Route::get('collections', [CollectionController::class, 'index'])->name('collections');
    Route::post('collections/offline', [CollectionController::class, 'offline'])->name('collections.offline');
    Route::post('invoices/{invoice}/payments', [CollectionController::class, 'pay'])->name('payments.store');
    Route::post('invoices/{invoice}/remind', [CollectionController::class, 'remind'])->name('invoices.remind');

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::post('expenses', [TransactionController::class, 'storeExpense'])->name('expenses.store');

    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll');
    Route::post('payroll', [PayrollController::class, 'process'])->name('payroll.process');
    Route::put('payroll/salaries', [PayrollController::class, 'saveSalaries'])->name('payroll.salaries');

    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets');
    Route::post('budgets', [BudgetController::class, 'store'])->name('budgets.store');

    Route::get('reports', [FinanceReportController::class, 'index'])->name('reports');
    Route::get('reports/export/{type}', [FinanceReportController::class, 'export'])->name('reports.export');

    Route::get('communications', fn () => view('finance.communications', [
        'announcements' => Announcement::with('user')->where('audience', 'all')->latest()->paginate(10),
    ]))->name('communications');
    Route::view('help', 'finance.help')->name('help');
});

// ---- Parent ----
Route::middleware(['auth', EnsureRole::class.':parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/', [ParentDashboardController::class, 'index'])->name('dashboard');

    Route::get('children', [ChildrenController::class, 'index'])->name('children');
    Route::get('children/{student}', [ChildrenController::class, 'show'])->name('children.show');

    Route::get('progress', [ProgressController::class, 'index'])->name('progress');
    Route::get('attendance', [ParentAttendanceController::class, 'index'])->name('attendance');

    Route::get('fees', [FeesController::class, 'index'])->name('fees');
    Route::get('fees/invoices/{invoice}', [FeesController::class, 'invoice'])->name('invoices.show');
    Route::get('fees/invoices/{invoice}/pay', [FeesController::class, 'pay'])->name('invoices.pay');
    Route::get('fees/payments/{payment}/receipt', [FeesController::class, 'receipt'])->name('receipts.show');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('announcements', fn () => view('parent.announcements', [
        'announcements' => Announcement::with('user')->whereIn('audience', ['all', 'parents'])->latest()->paginate(10),
    ]))->name('announcements');

    Route::get('messages', [MessageController::class, 'index'])->name('messages');
    Route::get('messages/new', [MessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{user}', [MessageController::class, 'show'])->name('messages.show');

    Route::view('help', 'parent.help')->name('help');
});

// ---- Student ----
Route::middleware(['auth', EnsureRole::class.':student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');

    Route::get('courses', [CourseController::class, 'index'])->name('courses');
    Route::get('courses/{subject}', [CourseController::class, 'show'])->name('courses.show');

    Route::get('assignments', [StudentAssignmentController::class, 'index'])->name('assignments');
    Route::get('assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');

    Route::get('timetable', [StudentTimetableController::class, 'index'])->name('timetable');
    Route::get('grades', [GradesController::class, 'index'])->name('grades');
    Route::get('attendance', [StudentAttendanceController::class, 'index'])->name('attendance');

    Route::get('announcements', fn () => view('student.announcements', [
        'announcements' => Announcement::with('user')->whereIn('audience', ['all', 'students'])->latest()->paginate(10),
    ]))->name('announcements');

    Route::get('messages', [MessageController::class, 'index'])->name('messages');
    Route::get('messages/new', [MessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{user}', [MessageController::class, 'show'])->name('messages.show');

    Route::view('help', 'student.help')->name('help');
});

require __DIR__.'/auth.php';

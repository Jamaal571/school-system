<?php

namespace App\Http\Controllers\StudentPortal;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends StudentBaseController
{
    public function index(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $me = $this->me();
        $month = $request->month ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth() : now()->startOfMonth();

        $all = Attendance::where('student_id', $me->id)->get()->countBy('status');
        $total = $all->sum();

        return view('student.attendance', [
            'month' => $month, 'all' => $all, 'rate' => $total ? round((($all['present'] ?? 0) + ($all['late'] ?? 0)) / $total * 100, 1) : null,
            'rows' => Attendance::where('student_id', $me->id)->whereDate('date', '>=', $month->toDateString())->whereDate('date', '<=', $month->copy()->endOfMonth()->toDateString())->orderByDesc('date')->get(),
        ]);
    }
}

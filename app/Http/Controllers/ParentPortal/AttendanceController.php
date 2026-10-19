<?php

namespace App\Http\Controllers\ParentPortal;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends ParentBaseController
{
    public function index(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $child = $this->child($request);
        $month = $request->month ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth() : now()->startOfMonth();

        $rows = Attendance::where('student_id', $child->id)->whereDate('date', '>=', $month->toDateString())->whereDate('date', '<=', $month->copy()->endOfMonth()->toDateString())
            ->orderByDesc('date')->get();
        $all = Attendance::where('student_id', $child->id)->get()->countBy('status');
        $total = $all->sum();

        return view('parent.attendance', [
            'child' => $child, 'children' => $this->children(), 'month' => $month, 'rows' => $rows, 'all' => $all,
            'rate' => $total ? round((($all['present'] ?? 0) + ($all['late'] ?? 0)) / $total * 100, 1) : null,
        ]);
    }
}

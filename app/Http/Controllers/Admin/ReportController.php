<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\StaffMember;
use App\Models\Student;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['students', 'attendance', 'fees', 'staff'], true), 404);
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'status' => 'nullable|in:paid,pending,overdue']);

        return response()->streamDownload(function () use ($type, $request) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 marker so Excel shows accents correctly
            $this->{$type}($out, $request);
            fclose($out);
        }, $type.'-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** Stops spreadsheet programs from running text that starts with = + - @ as a formula. */
    private function safe($value)
    {
        return is_string($value) && preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }

    private function put($out, array $row): void
    {
        fputcsv($out, array_map(fn ($v) => $this->safe($v), $row));
    }

    private function students($out, Request $request): void
    {
        $this->put($out, ['Admission no', 'First name', 'Last name', 'Gender', 'Date of birth', 'Class', 'Status', 'Guardian', 'Guardian phone']);
        Student::with(['guardians', 'enrollments.schoolClass'])->orderBy('id')->chunk(200, function ($students) use ($out) {
            foreach ($students as $s) {
                $g = $s->guardians->first();
                $this->put($out, [
                    $s->admission_no, $s->first_name, $s->last_name, $s->gender, $s->date_of_birth?->toDateString(),
                    $s->enrollments->sortByDesc('academic_year_id')->first()?->schoolClass?->name, $s->status,
                    $g?->full_name, $g?->phone,
                ]);
            }
        });
    }

    private function attendance($out, Request $request): void
    {
        $from = $request->from ?: now()->subDays(30)->toDateString();
        $to = $request->to ?: now()->toDateString();

        $this->put($out, ['Date', 'Class', 'Admission no', 'Student', 'Status']);
        Attendance::with(['student', 'schoolClass'])->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)
            ->orderBy('date')->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $a) {
                    $this->put($out, [$a->date->toDateString(), $a->schoolClass?->name, $a->student?->admission_no, $a->student?->full_name, $a->status]);
                }
            });
    }

    private function fees($out, Request $request): void
    {
        $this->put($out, ['Invoice', 'Admission no', 'Student', 'Description', 'Amount', 'Paid', 'Balance', 'Due', 'Status']);
        Invoice::with(['student', 'payments'])->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('id')->chunk(200, function ($invoices) use ($out) {
                foreach ($invoices as $i) {
                    $paid = (float) $i->payments->sum('amount');
                    $this->put($out, [
                        $i->number, $i->student?->admission_no, $i->student?->full_name, $i->description,
                        number_format((float) $i->amount, 2, '.', ''), number_format($paid, 2, '.', ''),
                        number_format(max((float) $i->amount - $paid, 0), 2, '.', ''), $i->due_on->toDateString(), $i->status,
                    ]);
                }
            });
    }

    private function staff($out, Request $request): void
    {
        $this->put($out, ['First name', 'Last name', 'Type', 'Email', 'Phone', 'Hired on', 'Status']);
        StaffMember::orderBy('id')->chunk(200, function ($members) use ($out) {
            foreach ($members as $m) {
                $this->put($out, [$m->first_name, $m->last_name, $m->type, $m->email, $m->phone, $m->hired_on?->toDateString(), $m->status]);
            }
        });
    }
}

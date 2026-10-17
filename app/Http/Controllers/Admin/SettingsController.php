<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $year = AcademicYear::current();

        return view('admin.settings.edit', ['year' => $year, 'term' => $year?->currentTerm()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'year_name' => 'required|string|max:20',
            'year_starts_on' => 'required|date',
            'year_ends_on' => 'required|date|after:year_starts_on',
            'term_name' => 'required|string|max:40',
            'term_starts_on' => 'required|date|after_or_equal:year_starts_on',
            'term_ends_on' => 'required|date|after:term_starts_on|before_or_equal:year_ends_on',
        ]);

        $year = AcademicYear::current() ?? new AcademicYear(['is_current' => true]);
        $year->fill(['name' => $data['year_name'], 'starts_on' => $data['year_starts_on'], 'ends_on' => $data['year_ends_on']])->save();

        $term = $year->currentTerm() ?? new Term(['academic_year_id' => $year->id]);
        $term->fill(['name' => $data['term_name'], 'starts_on' => $data['term_starts_on'], 'ends_on' => $data['term_ends_on']])->save();

        return redirect()->route('admin.settings')->with('status', 'Academic year and term saved.');
    }
}

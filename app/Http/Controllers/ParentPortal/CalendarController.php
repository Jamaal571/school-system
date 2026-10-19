<?php

namespace App\Http\Controllers\ParentPortal;

use App\Models\SchoolEvent;
use Illuminate\Http\Request;

class CalendarController extends ParentBaseController
{
    public function index()
    {
        return view('parent.calendar', ['events' => SchoolEvent::whereIn('audience', ['all', 'parents'])->upcoming()->get()->groupBy(fn ($e) => $e->starts_on->format('F Y'))]);
    }
}

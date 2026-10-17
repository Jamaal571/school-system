<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Fake data copied from the design. Replace each block with real queries later.
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        $head = ['name' => 'Dr. James Wilson', 'short' => 'Dr. Wilson', 'initials' => 'JW', 'role' => 'Head of School'];

        $stats = [
            ['label' => 'Active students',  'value' => '1,248',     'icon' => 'academic-cap', 'note' => '6.8% from last academic year', 'tone' => 'success', 'arrow' => true],
            ['label' => 'Staff & teachers', 'value' => '96',        'icon' => 'users',        'note' => '78 teachers · 18 support staff', 'tone' => 'muted',   'arrow' => false],
            ['label' => 'Daily attendance', 'value' => '96.4%',     'icon' => 'calendar-days', 'note' => '1.2 pts vs. previous school day', 'tone' => 'success', 'arrow' => true],
            ['label' => 'Term revenue',     'value' => '$1,284,000', 'icon' => 'wallet',      'note' => '91.7% of $1,400,000 budget',     'tone' => 'primary', 'arrow' => false],
        ];

        $attendance = [
            'labels'   => ['Mon 12', 'Tue 13', 'Wed 14', 'Thu 15', 'Fri 16'],
            'points'   => [95.0, 97.6, 95.2, 96.4],
            'forecast' => [96.4],
        ];

        $enrollment = [
            'labels' => ['2023', '2024', '2025', '2026'],
            'points' => [1105, 1135, 1170, 1250],
        ];

        $approvals = [
            ['title' => 'Science lab supplies',   'by' => 'Finance team',     'value' => '$4,800',  'status' => 'urgent'],
            ['title' => 'Annual leave · Sarah Lee', 'by' => 'Human resources', 'value' => '19–20 Oct', 'status' => 'pending'],
            ['title' => 'Library book fund',      'by' => 'Finance team',     'value' => '$2,400',  'status' => 'pending'],
            ['title' => 'Grade 9 field trip',     'by' => 'Academic office',  'value' => '$1,250',  'status' => 'pending'],
        ];

        $activity = [
            ['icon' => 'wallet', 'tone' => 'warning', 'title' => 'Budget request needs approval',
             'text' => 'Finance requested $4,800 for science lab supplies.', 'meta' => '10:12 AM · Finance'],
            ['icon' => 'shield-exclamation', 'tone' => 'danger', 'title' => 'Disciplinary note escalated',
             'text' => 'Grade 10B · Review requested by the year lead.', 'meta' => '9:45 AM · Student wellbeing'],
            ['icon' => 'check-circle', 'tone' => 'success', 'title' => 'Morning attendance complete',
             'text' => '1,203 of 1,248 students present across 42 classes.', 'meta' => '9:15 AM · Attendance office'],
        ];

        return view('admin.dashboard', compact('greeting', 'head', 'stats', 'attendance', 'enrollment', 'approvals', 'activity'));
    }
}

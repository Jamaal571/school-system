@props(['status'])
@php
    $styles = [
        'paid' => 'bg-success-soft text-success', 'active' => 'bg-success-soft text-success',
        'present' => 'bg-success-soft text-success', 'approved' => 'bg-success-soft text-success',
        'urgent' => 'bg-warning-soft text-warning', 'late' => 'bg-warning-soft text-warning',
        'overdue' => 'bg-danger-soft text-danger', 'absent' => 'bg-danger-soft text-danger',
        'rejected' => 'bg-danger-soft text-danger', 'withdrawn' => 'bg-danger-soft text-danger',
        'pending' => 'bg-primary-soft text-primary', 'graduated' => 'bg-primary-soft text-primary',
        'excused' => 'bg-primary-soft text-primary',
    ];
@endphp
<span {{ $attributes->class(['inline-flex rounded-md px-2.5 py-1 text-sm font-medium', $styles[$status] ?? 'bg-canvas text-ink-soft']) }}>{{ $slot }}</span>

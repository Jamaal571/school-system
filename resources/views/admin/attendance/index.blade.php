@php
    $options = [
        'present' => ['Present', 'peer-checked:bg-success'],
        'late' => ['Late', 'peer-checked:bg-warning'],
        'absent' => ['Absent', 'peer-checked:bg-danger'],
    ];
    $counts = $existing->countBy();
@endphp
<x-layouts.admin title="Attendance" crumb="Attendance">
    <x-page-header title="Attendance" :subtitle="$date->format('l, j F Y')" />

    <form method="GET" class="mt-6 flex flex-wrap items-end gap-3">
        <label class="block">
            <span class="text-sm font-medium">Class</span>
            <select name="class" onchange="this.form.submit()" class="mt-1.5 block rounded-xl border-line bg-white py-2.5 text-sm">
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}" @selected($class && $class->id === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="text-sm font-medium">Date</span>
            <input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ now()->toDateString() }}"
                   onchange="this.form.submit()" class="mt-1.5 block rounded-xl border-line bg-white px-4 py-2.5 text-sm">
        </label>
        <noscript><button class="rounded-xl border border-line bg-white px-5 py-2.5 text-sm">Load</button></noscript>
        @if ($existing->isNotEmpty())
            <p class="pb-2.5 text-sm text-ink-soft">
                Saved: {{ $counts['present'] ?? 0 }} present · {{ $counts['late'] ?? 0 }} late · {{ $counts['absent'] ?? 0 }} absent
            </p>
        @endif
    </form>

    @if ($class && $rows->isNotEmpty())
        <form method="POST" action="{{ route('admin.attendance.store') }}" class="mt-5">
            @csrf
            <input type="hidden" name="class" value="{{ $class->id }}">
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            <div class="overflow-hidden rounded-2xl border border-line bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <h2 class="text-lg font-semibold">{{ $class->name }} · {{ $rows->count() }} students</h2>
                    <button type="button" class="rounded-lg border border-line px-3 py-2 text-sm font-medium hover:bg-wash"
                            onclick="document.querySelectorAll('input[value=present]').forEach(i => i.checked = true)">Mark all present</button>
                </div>
                <ul class="divide-y divide-line">
                    @foreach ($rows as $e)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                            <div>
                                <p class="font-medium">{{ $e->student->full_name }}</p>
                                <p class="text-sm text-ink-soft">{{ $e->student->admission_no }}</p>
                            </div>
                            <div class="flex gap-2">
                                @foreach ($options as $value => [$label, $color])
                                    <label class="cursor-pointer">
                                        <input type="radio" class="peer sr-only" name="status[{{ $e->student_id }}]" value="{{ $value }}"
                                               @checked(($existing[$e->student_id] ?? 'present') === $value)>
                                        <span class="block rounded-lg border border-line px-3.5 py-1.5 text-sm text-ink-soft peer-checked:border-transparent peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-primary {{ $color }}">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <button class="mt-5 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Save attendance</button>
        </form>
    @else
        <p class="mt-8 rounded-2xl border border-line bg-white py-12 text-center text-ink-soft">No students are enrolled in this class yet.</p>
    @endif
</x-layouts.admin>

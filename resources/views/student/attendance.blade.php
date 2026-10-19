<x-layouts.student title="Attendance" crumb="Attendance">
    <x-page-header title="Attendance" :subtitle="$rate !== null ? $rate.'% overall' : 'No attendance recorded yet'" />
    <div class="mt-6 grid gap-5 sm:grid-cols-4">
        @foreach (['present' => 'text-success', 'late' => 'text-warning', 'absent' => 'text-danger', 'excused' => 'text-primary'] as $k => $color)
            <div class="rounded-2xl border border-line bg-white p-6"><p class="text-[15px] text-ink-soft">{{ ucfirst($k) }}</p><p class="mt-3 text-2xl font-semibold {{ $color }}">{{ $all[$k] ?? 0 }}</p></div>
        @endforeach
    </div>
    <form method="GET" class="mt-6 flex items-center gap-3"><input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()" class="rounded-xl border-line bg-white px-4 py-2 text-sm" aria-label="Month"><span class="text-sm text-ink-soft">{{ $month->format('F Y') }}</span></form>
    <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-white"><ul class="divide-y divide-line">
        @forelse ($rows as $r)<li class="flex items-center justify-between px-5 py-3.5"><span>{{ $r->date->format('l, j F') }}</span><x-badge :status="$r->status">{{ ucfirst($r->status) }}</x-badge></li>
        @empty<li class="px-5 py-12 text-center text-ink-soft">No attendance recorded for this month.</li>@endforelse
    </ul></div>
</x-layouts.student>

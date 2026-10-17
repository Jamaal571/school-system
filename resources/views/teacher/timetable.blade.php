@php $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday']; @endphp
<x-layouts.teacher title="Timetable" crumb="Timetable">
    <x-page-header title="Timetable" subtitle="Your weekly teaching schedule." />
    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach ($days as $n => $name)
            <section @class(['rounded-2xl border bg-white p-4', 'border-primary' => now()->dayOfWeekIso === $n, 'border-line' => now()->dayOfWeekIso !== $n])>
                <h2 class="font-semibold">{{ $name }} @if (now()->dayOfWeekIso === $n)<span class="text-sm font-normal text-primary">· today</span>@endif</h2>
                <ul class="mt-3 space-y-2">
                    @forelse ($slots[$n] ?? [] as $s)
                        <li class="rounded-xl bg-wash px-3 py-2.5">
                            <p class="text-sm font-semibold">{{ \Carbon\Carbon::parse($s->starts_at)->format('H:i') }}–{{ \Carbon\Carbon::parse($s->ends_at)->format('H:i') }}</p>
                            <p class="text-sm">{{ $s->schoolClass->name }} · {{ $s->subject->name }}</p>
                            <p class="text-xs text-ink-soft">Room {{ $s->room }}</p>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-ink-soft">No classes</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
</x-layouts.teacher>

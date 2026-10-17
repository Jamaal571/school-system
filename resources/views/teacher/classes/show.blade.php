<x-layouts.teacher :title="$class->name" crumb="My classes">
    <x-page-header :title="$class->name" :subtitle="$students->count().' students'">
        <a href="{{ route('teacher.classes') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Back</a>
        <a href="{{ route('teacher.attendance', ['class' => $class->id]) }}" class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Take attendance</a>
    </x-page-header>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[520px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft">
                <tr><th class="px-5 py-3 font-medium">Student</th><th class="px-5 py-3 font-medium">Attendance (30 days)</th><th class="px-5 py-3 font-medium">Average score</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($students as $s)
                    @php $a = $attendance[$s->id] ?? null; $score = $scores[$s->id] ?? null; @endphp
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5"><p class="font-semibold">{{ $s->full_name }}</p><p class="text-sm text-ink-soft">{{ $s->admission_no }}</p></td>
                        <td class="px-5 py-3.5">{{ $a ? round($a->attended / $a->total * 100).'%' : '—' }}</td>
                        <td class="px-5 py-3.5"><span @class(['font-medium', 'text-danger' => $score !== null && $score < 60])>{{ $score !== null ? $score.'%' : '—' }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.teacher>

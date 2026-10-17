<x-layouts.teacher title="Gradebook" crumb="Gradebook">
    <x-page-header title="Gradebook" subtitle="Scores for every student in a class. Click an assignment to enter scores." />

    <form method="GET" class="mt-6">
        <select name="class" onchange="this.form.submit()" class="rounded-xl border-line bg-white py-2.5 text-sm">
            @foreach ($classes as $c) <option value="{{ $c->id }}" @selected($class && $class->id === $c->id)>{{ $c->name }}</option> @endforeach
        </select>
    </form>

    @if ($class && $assignments->isNotEmpty())
        <div class="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
            <table class="w-full min-w-[640px] text-left text-[15px]">
                <thead class="bg-wash text-xs tracking-wide text-ink-soft">
                    <tr>
                        <th class="px-5 py-3 font-medium uppercase">Student</th>
                        @foreach ($assignments as $a)
                            <th class="px-4 py-3 font-medium"><a href="{{ route('teacher.assignments.grade', $a) }}" class="text-primary hover:underline">{{ $a->title }}</a><span class="block font-normal">/ {{ $a->max_score }}</span></th>
                        @endforeach
                        <th class="px-5 py-3 font-medium uppercase">Average</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($students as $s)
                        @php
                            $mine = $subs[$s->id] ?? collect();
                            $pcts = $assignments->map(fn ($a) => ($x = $mine[$a->id] ?? null) && $x->score !== null ? $x->score / max($a->max_score, 1) * 100 : null)->filter(fn ($v) => $v !== null);
                            $avg = $pcts->isEmpty() ? null : round($pcts->avg());
                        @endphp
                        <tr class="hover:bg-wash">
                            <td class="px-5 py-3 font-medium">{{ $s->full_name }}</td>
                            @foreach ($assignments as $a)
                                @php $x = $mine[$a->id] ?? null; @endphp
                                <td class="px-4 py-3">{{ $x && $x->score !== null ? (float) $x->score : ($x && $x->submitted_on ? 'To grade' : '—') }}</td>
                            @endforeach
                            <td class="px-5 py-3 font-semibold"><span @class(['text-danger' => $avg !== null && $avg < 60])>{{ $avg !== null ? $avg.'%' : '—' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="mt-6 rounded-2xl border border-line bg-white py-12 text-center text-ink-soft">No assignments for this class yet.</p>
    @endif
</x-layouts.teacher>

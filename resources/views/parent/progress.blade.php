<x-layouts.parent title="Academic progress" crumb="Academic progress">
    <x-page-header title="Academic progress" :subtitle="$child->full_name.' · graded work and upcoming assignments'" />
    <div class="mt-6"><x-child-switcher :children="$children" :child="$child" /></div>

    <div class="overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[560px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-5 py-3 font-medium">Subject</th><th class="px-5 py-3 font-medium">Teacher</th><th class="px-5 py-3 font-medium">Average</th><th class="px-5 py-3 font-medium">Latest assessment</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($subjects as $s)
                    <tr><td class="px-5 py-3.5 font-semibold">{{ $s['subject'] }}</td><td class="px-5 py-3.5">{{ $s['teacher'] ?? '—' }}</td>
                        <td class="px-5 py-3.5"><span class="rounded-md bg-success-soft px-2.5 py-1 text-sm font-medium text-success">{{ $s['avg'] }}% · {{ $s['letter'] }}</span></td>
                        <td class="px-5 py-3.5">{{ $s['latest']['title'] }} · {{ rtrim(rtrim(number_format($s['latest']['score'], 1), '0'), '.') }}/{{ $s['latest']['max'] }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-ink-soft">No graded work yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-8 text-xl font-semibold">All assignments</h2>
    <div class="mt-3 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[560px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-5 py-3 font-medium">Assignment</th><th class="px-5 py-3 font-medium">Subject</th><th class="px-5 py-3 font-medium">Due</th><th class="px-5 py-3 font-medium">Result</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($assignments as $a)
                    @php $s = $subs[$a->id] ?? null; @endphp
                    <tr><td class="px-5 py-3.5 font-semibold">{{ $a->title }}</td><td class="px-5 py-3.5">{{ $a->subject->name }}</td><td class="px-5 py-3.5">{{ $a->due_on->format('j M Y') }}</td>
                        <td class="px-5 py-3.5">@if ($s && $s->score !== null)<span class="font-medium">{{ rtrim(rtrim(number_format((float) $s->score, 1), '0'), '.') }} / {{ $a->max_score }}</span>@elseif ($s?->submitted_on)<span class="text-ink-soft">Handed in, waiting for marks</span>@elseif ($a->due_on->isFuture() || $a->due_on->isToday())<span class="text-primary">Upcoming</span>@else<span class="text-warning">Not handed in</span>@endif</td></tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-ink-soft">No assignments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.parent>

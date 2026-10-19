<x-layouts.student title="Grades & progress" crumb="Grades & progress">
    <x-page-header title="Grades &amp; progress" :subtitle="$average === null ? 'Your report card' : 'Overall average: '.$average.'%'" />
    <div class="mt-6 grid gap-5 sm:grid-cols-2">
        @forelse ($subjects as $s)
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-center justify-between"><h2 class="font-semibold">{{ $s['subject'] }}</h2><span class="rounded-md bg-success-soft px-2.5 py-1 text-sm font-medium text-success">{{ $s['avg'] }}% · {{ $s['letter'] }}</span></div>
                <p class="mt-1 text-sm text-ink-soft">{{ $s['teacher'] ?? '' }}</p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-canvas"><div class="h-full rounded-full bg-primary" style="width: {{ $s['avg'] }}%"></div></div>
            </section>
        @empty
            <p class="rounded-2xl border border-line bg-white py-12 text-center text-ink-soft sm:col-span-2">Your grades appear here once your teachers mark the first assignment.</p>
        @endforelse
    </div>
    @if ($work->isNotEmpty())
        <h2 class="mt-8 text-xl font-semibold">All graded work</h2>
        <div class="mt-3 overflow-x-auto rounded-2xl border border-line bg-white">
            <table class="w-full min-w-[560px] text-left text-[15px]">
                <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-5 py-3 font-medium">Assignment</th><th class="px-5 py-3 font-medium">Subject</th><th class="px-5 py-3 font-medium">Score</th><th class="px-5 py-3 font-medium">Feedback</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach ($work as $w)
                        <tr><td class="px-5 py-3.5 font-semibold"><a href="{{ route('student.assignments.show', $w['id']) }}" class="hover:text-primary">{{ $w['title'] }}</a></td><td class="px-5 py-3.5">{{ $w['subject'] }}</td>
                            <td class="px-5 py-3.5">{{ rtrim(rtrim(number_format($w['score'], 1), '0'), '.') }}/{{ $w['max'] }} <span class="text-sm text-ink-soft">({{ round($w['pct']) }}%)</span></td><td class="px-5 py-3.5 text-sm text-ink-soft">{{ $w['feedback'] ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.student>

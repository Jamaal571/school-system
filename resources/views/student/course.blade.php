@php $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri']; @endphp
<x-layouts.student :title="$subject->name" crumb="My courses">
    <x-page-header :title="$subject->name" :subtitle="$course['teacher'] ?? 'Teacher to be confirmed'">
        <a href="{{ route('student.courses') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">All courses</a>
    </x-page-header>

    @if ($slots->isNotEmpty())
        <section class="mt-6 rounded-2xl border border-line bg-white p-6"><h2 class="text-lg font-semibold">When you have this class</h2>
            <ul class="mt-3 flex flex-wrap gap-3 text-sm">@foreach ($slots as $s)<li class="rounded-xl bg-wash px-4 py-2.5"><span class="font-semibold">{{ $days[$s->day_of_week] ?? '' }}</span> {{ \Carbon\Carbon::parse($s->starts_at)->format('H:i') }}–{{ \Carbon\Carbon::parse($s->ends_at)->format('H:i') }} · {{ $s->room }}</li>@endforeach</ul></section>
    @endif

    <h2 class="mt-8 text-xl font-semibold">Assignments</h2>
    <div class="mt-3 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[560px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-5 py-3 font-medium">Assignment</th><th class="px-5 py-3 font-medium">Due</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Feedback</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($rows as $r)
                    <tr><td class="px-5 py-3.5 font-semibold"><a href="{{ route('student.assignments.show', $r['a']) }}" class="hover:text-primary">{{ $r['a']->title }}</a></td><td class="px-5 py-3.5">{{ $r['a']->due_on->format('j M Y') }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$r['status'][1]">{{ $r['status'][0] }}</x-badge></td><td class="px-5 py-3.5 text-sm text-ink-soft">{{ $r['sub']?->feedback ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-ink-soft">No assignments in this course yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.student>

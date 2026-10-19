<x-layouts.student title="Assignments" crumb="Assignments">
    <x-page-header title="Assignments" subtitle="Your deadlines and what you have handed in." />
    <div class="mt-6 flex flex-wrap gap-2">
        @foreach (['todo' => 'To do', 'done' => 'Handed in', 'all' => 'All'] as $k => $l)
            <a href="{{ route('student.assignments', ['show' => $k]) }}" @class(['rounded-lg px-4 py-2 text-sm font-medium', 'bg-primary-soft text-primary' => $show === $k, 'text-ink-soft hover:bg-white' => $show !== $k])>{{ $l }}</a>
        @endforeach
    </div>
    <div class="mt-4 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[620px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-5 py-3 font-medium">Assignment</th><th class="px-5 py-3 font-medium">Subject</th><th class="px-5 py-3 font-medium">Due</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 text-right font-medium">Action</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($rows as $r)
                    <tr class="hover:bg-wash"><td class="px-5 py-3.5 font-semibold">{{ $r['a']->title }}</td><td class="px-5 py-3.5">{{ $r['a']->subject->name }}</td><td class="px-5 py-3.5">{{ $r['a']->due_on->format('j M Y') }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$r['status'][1]">{{ $r['status'][0] }}</x-badge></td>
                        <td class="px-5 py-3.5 text-right"><a href="{{ route('student.assignments.show', $r['a']) }}" class="text-sm font-medium text-primary hover:underline">Open</a></td></tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-ink-soft">{{ $show === 'todo' ? 'Nothing to do. You are all caught up.' : 'Nothing here yet.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.student>

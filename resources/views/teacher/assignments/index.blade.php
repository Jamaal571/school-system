<x-layouts.teacher title="Assignments" crumb="Assignments">
    <x-page-header title="Assignments" subtitle="Everything you have set, newest due date first.">
        <a href="{{ route('teacher.assignments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="plus" class="h-5 w-5" /> Create assignment</a>
    </x-page-header>

    <form method="GET" class="mt-6 flex gap-3">
        <select name="class" onchange="this.form.submit()" class="rounded-xl border-line bg-white py-2.5 text-sm">
            <option value="">All my classes</option>
            @foreach ($classes as $c) <option value="{{ $c->id }}" @selected(request('class') == $c->id)>{{ $c->name }}</option> @endforeach
        </select>
    </form>

    <div class="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[720px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft">
                <tr><th class="px-5 py-3 font-medium">Assignment</th><th class="px-5 py-3 font-medium">Class</th><th class="px-5 py-3 font-medium">Due</th><th class="px-5 py-3 font-medium">Submitted</th><th class="px-5 py-3 font-medium">Graded</th><th class="px-5 py-3 text-right font-medium">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($assignments as $a)
                    @php $size = $sizes[$a->school_class_id] ?? 0; @endphp
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5 font-semibold">{{ $a->title }}<p class="text-sm font-normal text-ink-soft">Out of {{ $a->max_score }}</p></td>
                        <td class="px-5 py-3.5">{{ $a->schoolClass->name }}</td>
                        <td class="px-5 py-3.5">{{ $a->due_on->format('j M Y') }}</td>
                        <td class="px-5 py-3.5">{{ $a->submitted_count }} / {{ $size }}</td>
                        <td class="px-5 py-3.5">{{ $a->graded_count }} / {{ $size }}</td>
                        <td class="px-5 py-3.5 text-right"><a href="{{ route('teacher.assignments.grade', $a) }}" class="text-sm font-medium text-primary hover:underline">Grade</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-ink-soft">No assignments yet. Create the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $assignments->links() }}</div>
</x-layouts.teacher>

<x-layouts.admin title="Students" crumb="Students">
    <x-page-header title="Students" :subtitle="number_format($students->total()).' '.\Illuminate\Support\Str::plural('student', $students->total())">
        <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">
            <x-app-icon name="user-plus" class="h-5 w-5" /> Add student
        </a>
    </x-page-header>

    <form method="GET" class="mt-6 flex flex-wrap gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or admission number"
               class="min-w-[240px] flex-1 rounded-xl border-line bg-white px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
        <select name="class" class="rounded-xl border-line bg-white py-2.5 text-sm">
            <option value="">All classes</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}" @selected(request('class') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border-line bg-white py-2.5 text-sm">
            <option value="">Any status</option>
            @foreach (['active', 'graduated', 'withdrawn'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="rounded-xl border border-line bg-white px-5 py-2.5 text-sm font-medium hover:bg-wash">Filter</button>
        @if (request()->hasAny(['q', 'class', 'status']))
            <a href="{{ route('admin.students.index') }}" class="px-2 py-2.5 text-sm text-ink-soft hover:text-ink">Clear</a>
        @endif
    </form>

    <div class="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[640px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft">
                <tr>
                    <th class="px-5 py-3 font-medium">Student</th>
                    <th class="px-5 py-3 font-medium">Class</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($students as $s)
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('admin.students.show', $s) }}" class="font-semibold hover:text-primary">{{ $s->full_name }}</a>
                            <p class="text-sm text-ink-soft">{{ $s->admission_no }}</p>
                        </td>
                        <td class="px-5 py-3.5">{{ $s->enrollments->first()?->schoolClass?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$s->status">{{ ucfirst($s->status) }}</x-badge></td>
                        <td class="px-5 py-3.5 text-right text-sm font-medium">
                            <a href="{{ route('admin.students.show', $s) }}" class="text-primary hover:underline">View</a>
                            <a href="{{ route('admin.students.edit', $s) }}" class="ml-4 text-primary hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-12 text-center text-ink-soft">No students match your search.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $students->links() }}</div>
</x-layouts.admin>

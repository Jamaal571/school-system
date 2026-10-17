<x-layouts.admin title="Staff & teachers" crumb="Staff & teachers">
    <x-page-header title="Staff & teachers" :subtitle="$staff->total().' '.\Illuminate\Support\Str::plural('person', $staff->total())">
        <a href="{{ route('admin.staff.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">
            <x-app-icon name="user-plus" class="h-5 w-5" /> Add staff member
        </a>
    </x-page-header>

    <form method="GET" class="mt-6 flex flex-wrap gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or email"
               class="min-w-[240px] flex-1 rounded-xl border-line bg-white px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
        <select name="type" class="rounded-xl border-line bg-white py-2.5 text-sm">
            <option value="">Teachers and support</option>
            <option value="teacher" @selected(request('type') === 'teacher')>Teachers</option>
            <option value="support" @selected(request('type') === 'support')>Support staff</option>
        </select>
        <button class="rounded-xl border border-line bg-white px-5 py-2.5 text-sm font-medium hover:bg-wash">Filter</button>
        @if (request()->hasAny(['q', 'type'])) <a href="{{ route('admin.staff.index') }}" class="px-2 py-2.5 text-sm text-ink-soft hover:text-ink">Clear</a> @endif
    </form>

    <div class="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[680px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft">
                <tr>
                    <th class="px-5 py-3 font-medium">Name</th><th class="px-5 py-3 font-medium">Role</th>
                    <th class="px-5 py-3 font-medium">Contact</th><th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($staff as $m)
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5 font-semibold">{{ $m->full_name }}</td>
                        <td class="px-5 py-3.5">{{ $m->type === 'teacher' ? 'Teacher' : 'Support staff' }}
                            @if ($m->classes_count) <p class="text-sm text-ink-soft">Homeroom: {{ $m->classes_count }} {{ \Illuminate\Support\Str::plural('class', $m->classes_count) }}</p> @endif</td>
                        <td class="px-5 py-3.5">{{ $m->email ?? '—' }}<p class="text-sm text-ink-soft">{{ $m->phone }}</p></td>
                        <td class="px-5 py-3.5"><x-badge :status="$m->status">{{ ucfirst($m->status) }}</x-badge></td>
                        <td class="px-5 py-3.5 text-right text-sm font-medium">
                            <a href="{{ route('admin.staff.edit', $m) }}" class="text-primary hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.staff.destroy', $m) }}" class="ml-4 inline"
                                  onsubmit="return confirm('Delete {{ $m->full_name }}?')">
                                @csrf @method('DELETE')
                                <button class="text-danger hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-ink-soft">No staff match your search.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $staff->links() }}</div>
</x-layouts.admin>

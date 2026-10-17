<x-layouts.admin title="Academics" crumb="Academics">
    <x-page-header title="Academics" subtitle="Classes and subjects for this academic year." />

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <section class="xl:col-span-2">
            <div class="overflow-x-auto rounded-2xl border border-line bg-white">
                <table class="w-full min-w-[520px] text-left text-[15px]">
                    <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft">
                        <tr><th class="px-5 py-3 font-medium">Class</th><th class="px-5 py-3 font-medium">Homeroom teacher</th><th class="px-5 py-3 font-medium">Students</th><th class="px-5 py-3 font-medium"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($classes as $c)
                            <tr class="hover:bg-wash">
                                <td class="px-5 py-3.5 font-semibold">{{ $c->name }}</td>
                                <td class="px-5 py-3.5">{{ $c->homeroomTeacher?->full_name ?? '—' }}</td>
                                <td class="px-5 py-3.5">{{ $c->enrollments_count }} / {{ $c->capacity }}</td>
                                <td class="px-5 py-3.5 text-right"><a href="{{ route('admin.students.index', ['class' => $c->id]) }}" class="text-sm font-medium text-primary hover:underline">View students</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-ink-soft">No classes yet. Add the first one on the right.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="space-y-5">
            <form method="POST" action="{{ route('admin.academics.classes.store') }}" class="space-y-4 rounded-2xl border border-line bg-white p-6">
                @csrf
                <h2 class="text-lg font-semibold">Add a class</h2>
                <x-form-field name="name" label="Name" placeholder="Grade 7 D" required />
                <x-form-field name="grade_level" type="number" min="1" max="12" label="Grade level" required />
                <label class="block">
                    <span class="text-sm font-medium">Homeroom teacher</span>
                    <select name="homeroom_teacher_id" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                        <option value="">None yet</option>
                        @foreach ($teachers as $t) <option value="{{ $t->id }}" @selected(old('homeroom_teacher_id') == $t->id)>{{ $t->full_name }}</option> @endforeach
                    </select>
                </label>
                <button class="w-full rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Add class</button>
            </form>

            <section class="rounded-2xl border border-line bg-white p-6">
                <h2 class="text-lg font-semibold">Subjects</h2>
                <ul class="mt-3 divide-y divide-line text-[15px]">
                    @forelse ($subjects as $s)
                        <li class="flex justify-between py-2.5"><span>{{ $s->name }}</span><span class="text-sm text-ink-soft">{{ $s->code }}</span></li>
                    @empty
                        <li class="py-4 text-center text-sm text-ink-soft">No subjects yet.</li>
                    @endforelse
                </ul>
                <form method="POST" action="{{ route('admin.academics.subjects.store') }}" class="mt-4 space-y-3 border-t border-line pt-4">
                    @csrf
                    <x-form-field name="name" label="Subject name" required />
                    <x-form-field name="code" label="Code" placeholder="MAT1" required />
                    <button class="w-full rounded-xl border border-line px-5 py-2.5 text-sm font-medium hover:bg-wash">Add subject</button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.admin>

<x-layouts.admin title="Add new user" crumb="Add new user">
    <x-page-header title="Add new user" subtitle="Create a login for a teacher, finance officer, parent, student or another admin." />

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 rounded-2xl border border-line bg-white p-6 xl:col-span-2" x-data="{ role: '{{ old('role', 'admin') }}' }">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form-field name="name" label="Full name" required />
                <x-form-field name="email" type="email" label="Email" required />
                <label class="block">
                    <span class="text-sm font-medium">Role</span>
                    <select name="role" x-model="role" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                        @foreach ($roles as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
                    </select>
                </label>
                <x-form-field name="password" type="password" label="Temporary password (8+ characters)" autocomplete="new-password" required />
            </div>

            <div x-show="['student', 'parent', 'teacher'].includes(role)" style="display:none">
                <x-form-field name="link" label="Link this login to" placeholder="NW00012 or teacher@school.org" />
                <p class="mt-1.5 text-sm text-ink-soft">
                    <span x-show="role === 'student'">Enter the student's admission number. The student then sees their own dashboard.</span>
                    <span x-show="role === 'parent'">Enter one child's admission number. The parent then sees their children.</span>
                    <span x-show="role === 'teacher'">Enter the teacher's email from Staff &amp; teachers. The teacher then sees their own classes.</span>
                </p>
            </div>

            <p class="text-sm text-ink-soft">Choose the role carefully: it decides which area of the system the person can open.</p>
            <button class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Create account</button>
        </form>

        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Recently added</h2>
            <ul class="mt-3 divide-y divide-line text-[15px]">
                @foreach ($users as $u)
                    <li class="py-2.5"><p class="font-medium">{{ $u->name }}</p><p class="text-sm text-ink-soft">{{ $u->email }} · {{ $roles[$u->role] ?? $u->role }}</p></li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.admin>

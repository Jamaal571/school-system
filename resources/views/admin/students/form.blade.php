@php $editing = $student->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit student' : 'Add student'" crumb="Students">
    <x-page-header :title="$editing ? 'Edit '.$student->full_name : 'Add student'"
                   :subtitle="$editing ? $student->admission_no : 'The admission number is created automatically.'">
        <a href="{{ $editing ? route('admin.students.show', $student) : route('admin.students.index') }}"
           class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Cancel</a>
    </x-page-header>

    <form method="POST" action="{{ $editing ? route('admin.students.update', $student) : route('admin.students.store') }}"
          class="mt-6 max-w-3xl space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Student</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form-field name="first_name" label="First name" :value="$student->first_name" required />
                <x-form-field name="last_name" label="Last name" :value="$student->last_name" required />
                <label class="block">
                    <span class="text-sm font-medium">Gender</span>
                    <select name="gender" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                        <option value="">Not set</option>
                        @foreach (['male' => 'Male', 'female' => 'Female'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('gender', $student->gender) === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </label>
                <x-form-field name="date_of_birth" type="date" label="Date of birth" :value="$student->date_of_birth?->toDateString()" />
                <label class="block">
                    <span class="text-sm font-medium">Class</span>
                    <select name="school_class_id" required class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                        <option value="">Choose a class</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" @selected(old('school_class_id', $classId) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('school_class_id') <span class="mt-1 block text-sm text-danger">{{ $message }}</span> @enderror
                </label>
                @if ($editing)
                    <label class="block">
                        <span class="text-sm font-medium">Status</span>
                        <select name="status" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                            @foreach (['active', 'graduated', 'withdrawn'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $student->status) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Parent or guardian</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form-field name="guardian_first_name" label="First name" :value="$guardian?->first_name" />
                <x-form-field name="guardian_last_name" label="Last name" :value="$guardian?->last_name" />
                <x-form-field name="guardian_phone" label="Phone" :value="$guardian?->phone" />
                <label class="block">
                    <span class="text-sm font-medium">Relationship</span>
                    <select name="guardian_relationship" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                        <option value="">Not set</option>
                        @foreach (['Mother', 'Father', 'Guardian'] as $r)
                            <option value="{{ $r }}" @selected(old('guardian_relationship', $relationship) === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </section>

        <button class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">
            {{ $editing ? 'Save changes' : 'Add student' }}
        </button>
    </form>
</x-layouts.admin>

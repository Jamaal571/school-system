<x-layouts.teacher title="Create assignment" crumb="Assignments">
    <x-page-header title="Create assignment">
        <a href="{{ route('teacher.assignments') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Cancel</a>
    </x-page-header>
    <form method="POST" action="{{ route('teacher.assignments.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-2xl border border-line bg-white p-6">
        @csrf
        <x-form-field name="title" label="Title" placeholder="Fractions practice" required />
        <label class="block">
            <span class="text-sm font-medium">Class</span>
            <select name="school_class_id" required class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                <option value="">Choose a class</option>
                @foreach ($classes as $c) <option value="{{ $c->id }}" @selected(old('school_class_id') == $c->id)>{{ $c->name }}</option> @endforeach
            </select>
            @error('school_class_id') <span class="mt-1 block text-sm text-danger">{{ $message }}</span> @enderror
        </label>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form-field name="due_on" type="date" label="Due date" :value="now()->addWeek()->toDateString()" min="{{ now()->toDateString() }}" required />
            <x-form-field name="max_score" type="number" label="Out of (points)" :value="20" min="1" required />
        </div>
        <button class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Create assignment</button>
    </form>
</x-layouts.teacher>

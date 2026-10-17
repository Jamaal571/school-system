<x-layouts.admin title="Settings" crumb="Settings">
    <x-page-header title="Settings" subtitle="The academic year and term shown across the system." />

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 max-w-3xl space-y-6">
        @csrf @method('PUT')
        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Academic year</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <x-form-field name="year_name" label="Name" placeholder="2026–2027" :value="$year?->name" required />
                <x-form-field name="year_starts_on" type="date" label="Starts" :value="$year?->starts_on->toDateString()" required />
                <x-form-field name="year_ends_on" type="date" label="Ends" :value="$year?->ends_on->toDateString()" required />
            </div>
        </section>
        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Current term</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <x-form-field name="term_name" label="Name" placeholder="Term 1" :value="$term?->name" required />
                <x-form-field name="term_starts_on" type="date" label="Starts" :value="$term?->starts_on->toDateString()" required />
                <x-form-field name="term_ends_on" type="date" label="Ends" :value="$term?->ends_on->toDateString()" required />
            </div>
        </section>
        <button class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Save settings</button>
    </form>
</x-layouts.admin>

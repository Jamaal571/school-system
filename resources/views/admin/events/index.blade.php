<x-layouts.admin title="School calendar" crumb="School calendar">
    <x-page-header title="School calendar" subtitle="Events and meetings shown to parents and teachers." />
    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <section class="rounded-2xl border border-line bg-white p-6"><h2 class="text-lg font-semibold">Upcoming</h2>
                <ul class="mt-3 divide-y divide-line">
                    @forelse ($upcoming as $e)
                        <li class="flex items-start justify-between gap-3 py-3"><div><p class="font-semibold">{{ $e->title }}</p><p class="text-sm text-ink-soft">{{ $e->when }}@if ($e->location) · {{ $e->location }}@endif · for {{ $e->audience }}</p></div>
                            <form method="POST" action="{{ route('admin.events.destroy', $e) }}" onsubmit="return confirm('Remove this event?')">@csrf @method('DELETE')<button class="text-sm font-medium text-danger hover:underline">Remove</button></form></li>
                    @empty
                        <li class="py-8 text-center text-sm text-ink-soft">No upcoming events. Add one on the right.</li>
                    @endforelse
                </ul></section>
            @if ($past->isNotEmpty())
                <section class="rounded-2xl border border-line bg-white p-6"><h2 class="text-lg font-semibold">Recently finished</h2>
                    <ul class="mt-3 divide-y divide-line text-[15px]">@foreach ($past as $e)<li class="flex justify-between py-2.5"><span>{{ $e->title }}</span><span class="text-sm text-ink-soft">{{ $e->when }}</span></li>@endforeach</ul></section>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.events.store') }}" class="h-fit space-y-4 rounded-2xl border border-line bg-white p-6">
            @csrf
            <h2 class="text-lg font-semibold">Add an event</h2>
            <x-form-field name="title" label="Title" required />
            <div class="grid grid-cols-2 gap-3"><x-form-field name="starts_on" type="date" label="Starts" :value="now()->addDay()->toDateString()" required /><x-form-field name="ends_on" type="date" label="Ends (optional)" /></div>
            <div class="grid grid-cols-2 gap-3"><x-form-field name="time" label="Time" placeholder="9:00 AM" /><x-form-field name="location" label="Place" placeholder="Main hall" /></div>
            <x-form-field name="description" label="Details (optional)" />
            <div class="grid grid-cols-2 gap-3">
                <label class="block"><span class="text-sm font-medium">Type</span><select name="type" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm"><option value="event">Event</option><option value="meeting">Meeting</option><option value="holiday">Holiday</option></select></label>
                <label class="block"><span class="text-sm font-medium">Shown to</span><select name="audience" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm"><option value="all">Everyone</option><option value="parents">Parents</option><option value="students">Students</option><option value="teachers">Teachers</option></select></label>
            </div>
            <button class="w-full rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Add to calendar</button>
        </form>
    </div>
</x-layouts.admin>

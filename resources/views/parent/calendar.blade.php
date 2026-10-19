<x-layouts.parent title="School calendar" crumb="School calendar">
    <x-page-header title="School calendar" subtitle="Upcoming events and meetings." />
    <div class="mt-6 max-w-3xl space-y-8">
        @forelse ($events as $month => $list)
            <section><h2 class="text-lg font-semibold">{{ $month }}</h2>
                <ul class="mt-3 divide-y divide-line overflow-hidden rounded-2xl border border-line bg-white">
                    @foreach ($list as $e)
                        <li class="flex gap-4 px-5 py-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><x-app-icon :name="$e->type === 'meeting' ? 'users' : 'calendar'" class="h-5 w-5" /></span>
                            <div><p class="font-semibold">{{ $e->title }}</p><p class="text-sm text-ink-soft">{{ $e->when }}@if ($e->location) · {{ $e->location }}@endif</p>@if ($e->description)<p class="mt-1 text-sm text-body">{{ $e->description }}</p>@endif</div></li>
                    @endforeach
                </ul></section>
        @empty
            <p class="rounded-2xl border border-line bg-white py-12 text-center text-ink-soft">No upcoming events.</p>
        @endforelse
    </div>
</x-layouts.parent>

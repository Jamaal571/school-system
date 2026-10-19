<x-layouts.finance title="Communications" crumb="Communications">
    <x-page-header title="Communications" subtitle="Announcements from the school." />
    <div class="mt-6 max-w-3xl space-y-4">
        @forelse ($announcements as $a)
            <article class="rounded-2xl border border-line bg-white p-6">
                <h2 class="text-lg font-semibold">{{ $a->title }}</h2>
                <p class="mt-3 whitespace-pre-line text-[15px] text-body">{{ $a->body }}</p>
                <p class="mt-4 text-sm text-ink-soft">{{ $a->user?->name ?? 'Admin' }} · {{ $a->created_at->diffForHumans() }}</p>
            </article>
        @empty
            <p class="rounded-2xl border border-line bg-white py-12 text-center text-ink-soft">No announcements yet.</p>
        @endforelse
        {{ $announcements->links() }}
    </div>
</x-layouts.finance>

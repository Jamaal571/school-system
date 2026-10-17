<x-layouts.admin title="Communications" crumb="Communications">
    <x-page-header title="Communications" subtitle="Announcements to the whole school or to one group." />

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <form method="POST" action="{{ route('admin.communications.store') }}" class="h-fit space-y-4 rounded-2xl border border-line bg-white p-6 xl:order-2">
            @csrf
            <h2 class="text-lg font-semibold">New announcement</h2>
            <x-form-field name="title" label="Title" required />
            <label class="block">
                <span class="text-sm font-medium">Message</span>
                <textarea name="body" rows="5" required class="mt-1.5 w-full rounded-xl border-line px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">{{ old('body') }}</textarea>
                @error('body') <span class="mt-1 block text-sm text-danger">{{ $message }}</span> @enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium">Send to</span>
                <select name="audience" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                    @foreach (['all' => 'Everyone', 'teachers' => 'Teachers', 'parents' => 'Parents', 'students' => 'Students'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('audience') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </label>
            <button class="w-full rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Post announcement</button>
            <p class="text-xs text-ink-soft">Announcements are saved here now. Teachers, parents and students will see them on their own dashboards once those are built.</p>
        </form>

        <section class="space-y-4 xl:order-1 xl:col-span-2">
            @forelse ($announcements as $a)
                <article class="rounded-2xl border border-line bg-white p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-lg font-semibold">{{ $a->title }}</h2>
                        <x-badge status="pending">{{ ['all' => 'Everyone', 'teachers' => 'Teachers', 'parents' => 'Parents', 'students' => 'Students'][$a->audience] ?? $a->audience }}</x-badge>
                    </div>
                    <p class="mt-3 whitespace-pre-line text-[15px] text-body">{{ $a->body }}</p>
                    <p class="mt-4 text-sm text-ink-soft">{{ $a->user?->name ?? 'Admin' }} · {{ $a->created_at->diffForHumans() }}</p>
                </article>
            @empty
                <p class="rounded-2xl border border-line bg-white py-12 text-center text-ink-soft">No announcements yet. Write the first one.</p>
            @endforelse
            {{ $announcements->links() }}
        </section>
    </div>
</x-layouts.admin>

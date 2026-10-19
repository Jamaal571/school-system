<x-dynamic-component :component="'layouts.'.$role" :title="'Messages with '.$other->name" crumb="Messages">
    <x-page-header :title="$other->name" subtitle="Conversation">
        <a href="{{ route($role.'.messages') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">All messages</a>
    </x-page-header>
    <div class="mt-6 max-w-2xl space-y-3">
        @forelse ($messages as $m)
            @php $mine = $m->from_user_id === auth()->id(); @endphp
            <div @class(['flex', 'justify-end' => $mine])><div @class(['max-w-[85%] rounded-2xl px-4 py-3 text-[15px]', 'bg-primary text-white' => $mine, 'border border-line bg-white' => ! $mine])>
                <p class="whitespace-pre-line">{{ $m->body }}</p><p @class(['mt-1 text-xs', 'text-white/80' => $mine, 'text-ink-soft' => ! $mine])>{{ $m->created_at->format('j M, g:i A') }}</p></div></div>
        @empty
            <p class="rounded-2xl border border-line bg-white py-8 text-center text-ink-soft">No messages yet. Write the first one below.</p>
        @endforelse

        <form method="POST" action="{{ route($role.'.messages.store') }}" class="mt-4 space-y-3 rounded-2xl border border-line bg-white p-4">
            @csrf <input type="hidden" name="to_user_id" value="{{ $other->id }}">
            <label class="block"><span class="sr-only">Your reply</span><textarea name="body" rows="3" required placeholder="Write a reply" class="w-full rounded-xl border-line px-4 py-2.5 text-sm focus:border-primary focus:ring-primary"></textarea></label>
            @error('body') <p class="text-sm text-danger">{{ $message }}</p> @enderror
            <button class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Send</button>
        </form>
    </div>
</x-dynamic-component>

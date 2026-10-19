<x-dynamic-component :component="'layouts.'.$role" title="Messages" crumb="Messages">
    <x-page-header title="Messages" :subtitle="$role !== 'teacher' ? 'Conversations with your children’s teachers.' : 'Conversations with parents.'">
        <a href="{{ route($role.'.messages.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="plus" class="h-5 w-5" /> New message</a>
    </x-page-header>
    <ul class="mt-6 divide-y divide-line overflow-hidden rounded-2xl border border-line bg-white">
        @forelse ($threads as $id => $t)
            @php $person = $people[$id] ?? null; @endphp
            @if ($person)
                <li><a href="{{ route($role.'.messages.show', $person) }}" class="flex items-start justify-between gap-4 px-5 py-4 hover:bg-wash">
                    <div class="min-w-0"><p @class(['font-semibold', 'text-primary' => $t['unread']])>{{ $person->name }}</p><p class="mt-1 truncate text-sm text-ink-soft">{{ $t['last']->from_user_id === auth()->id() ? 'You: ' : '' }}{{ \Illuminate\Support\Str::limit($t['last']->body, 100) }}</p></div>
                    <div class="shrink-0 text-right text-xs text-ink-soft">{{ $t['last']->created_at->diffForHumans(null, true) }}@if ($t['unread'])<p class="mt-1 rounded-full bg-primary px-2 py-0.5 text-white">{{ $t['unread'] }} new</p>@endif</div></a></li>
            @endif
        @empty
            <li class="px-5 py-12 text-center text-ink-soft">No messages yet. Press New message to start a conversation.</li>
        @endforelse
    </ul>
</x-dynamic-component>

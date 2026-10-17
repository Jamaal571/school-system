@php
    $tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'];
@endphp
<x-layouts.admin title="Approvals" crumb="Approvals">
    <x-page-header title="Approvals" subtitle="Budgets, leave and trips waiting for your decision." />

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.approvals', ['status' => $key]) }}"
               @class(['rounded-lg px-4 py-2 text-sm font-medium',
                       'bg-primary-soft text-primary' => $tab === $key,
                       'text-ink-soft hover:bg-white' => $tab !== $key])>
                {{ $label }}
                <span class="ml-1 text-xs">{{ $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0) }}</span>
            </a>
        @endforeach
    </div>

    <div class="mt-4 space-y-4">
        @forelse ($requests as $r)
            <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-line bg-white p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-lg font-semibold">{{ $r->title }}</h2>
                        @if ($r->priority === 'urgent' && $r->status === 'pending') <x-badge status="urgent">Urgent</x-badge> @endif
                        @if ($r->status !== 'pending') <x-badge :status="$r->status">{{ ucfirst($r->status) }}</x-badge> @endif
                    </div>
                    <p class="mt-1 text-sm text-ink-soft">
                        {{ ucfirst($r->type) }} · {{ $r->department }}
                        @if ($r->amount !== null) · ${{ number_format((float) $r->amount) }} @endif
                        @if ($r->starts_on) · {{ $r->starts_on->format('j M') }}@if ($r->ends_on)–{{ $r->ends_on->format('j M') }}@endif @endif
                        · requested {{ $r->created_at->diffForHumans() }}
                    </p>
                </div>

                @if ($r->status === 'pending')
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('admin.approvals.decide', [$r, 'reject']) }}"
                              onsubmit="return confirm('Reject this request?')">
                            @csrf
                            <button class="rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-danger hover:bg-danger-soft">Reject</button>
                        </form>
                        <form method="POST" action="{{ route('admin.approvals.decide', [$r, 'approve']) }}">
                            @csrf
                            <button class="rounded-xl bg-success px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Approve</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <p class="rounded-2xl border border-line bg-white py-12 text-center text-ink-soft">Nothing here. New requests appear in this list.</p>
        @endforelse
    </div>
</x-layouts.admin>

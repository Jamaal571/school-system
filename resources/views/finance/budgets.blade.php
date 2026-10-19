<x-layouts.finance title="Budgets" crumb="Budgets">
    <x-page-header title="Budgets" subtitle="Budget requests sent to the head of school for approval." />

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <div class="flex flex-wrap gap-2">
                @foreach ([null => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $k => $l)
                    <a href="{{ route('finance.budgets', $k ? ['status' => $k] : []) }}" @class(['rounded-lg px-4 py-2 text-sm font-medium', 'bg-primary-soft text-primary' => $status === $k, 'text-ink-soft hover:bg-white' => $status !== $k])>{{ $l }}</a>
                @endforeach
            </div>
            @forelse ($requests as $r)
                <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white p-5">
                    <div>
                        <div class="flex flex-wrap items-center gap-3"><h2 class="text-lg font-semibold">{{ $r->title }}</h2>@if ($r->priority === 'urgent' && $r->status === 'pending')<x-badge status="urgent">Urgent</x-badge>@endif</div>
                        <p class="mt-1 text-sm text-ink-soft">${{ number_format((float) $r->amount) }} · requested {{ $r->created_at->diffForHumans() }}@if ($r->decided_at) · decided {{ $r->decided_at->format('j M') }}@endif</p>
                    </div>
                    <x-badge :status="$r->status">{{ ucfirst($r->status) }}</x-badge>
                </article>
            @empty
                <p class="rounded-2xl border border-line bg-white py-12 text-center text-ink-soft">No budget requests here.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('finance.budgets.store') }}" class="h-fit space-y-4 rounded-2xl border border-line bg-white p-6">
            @csrf
            <h2 class="text-lg font-semibold">New budget request</h2>
            <x-form-field name="title" label="What is it for?" placeholder="Science lab supplies" required />
            <x-form-field name="amount" type="number" step="0.01" label="Amount ($)" required />
            <label class="block"><span class="text-sm font-medium">Priority</span>
                <select name="priority" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm"><option value="normal">Normal</option><option value="urgent">Urgent</option></select></label>
            <button class="w-full rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Send for approval</button>
            <p class="text-xs text-ink-soft">The head of school approves or rejects it under Approvals.</p>
        </form>
    </div>
</x-layouts.finance>

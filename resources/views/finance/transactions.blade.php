<x-layouts.finance title="Transactions" crumb="Transactions">
    <div x-data="{ open: {{ $errors->hasAny(['category', 'description', 'amount', 'spent_on']) ? 'true' : 'false' }} }">
        <x-page-header title="Transactions" subtitle="Money in from fees and money out from expenses and payroll.">
            <button type="button" @click="open = ! open" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="plus" class="h-5 w-5" /> Record expense</button>
        </x-page-header>

        <form x-show="open" x-transition style="display:none" method="POST" action="{{ route('finance.expenses.store') }}" class="mt-5 grid gap-4 rounded-2xl border border-line bg-white p-6 sm:grid-cols-2 xl:grid-cols-5 xl:items-end">
            @csrf
            <label class="block"><span class="text-sm font-medium">Category</span>
                <select name="category" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">@foreach ($categories as $c)<option value="{{ $c }}" @selected(old('category') === $c)>{{ ucfirst($c) }}</option>@endforeach</select></label>
            <x-form-field name="description" label="Description" placeholder="Lab equipment" required />
            <x-form-field name="amount" type="number" step="0.01" label="Amount ($)" required />
            <x-form-field name="spent_on" type="date" label="Date" :value="now()->toDateString()" max="{{ now()->toDateString() }}" required />
            <button class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Record expense</button>
        </form>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-3">
        <div class="rounded-2xl border border-line bg-white p-6"><p class="text-[15px] text-ink-soft">Money in</p><p class="mt-3 text-2xl font-semibold text-success">${{ number_format($in) }}</p></div>
        <div class="rounded-2xl border border-line bg-white p-6"><p class="text-[15px] text-ink-soft">Money out</p><p class="mt-3 text-2xl font-semibold text-danger">${{ number_format($out) }}</p></div>
        <div class="rounded-2xl border border-line bg-white p-6"><p class="text-[15px] text-ink-soft">Net</p><p class="mt-3 text-2xl font-semibold">{{ $in - $out < 0 ? '−' : '' }}${{ number_format(abs($in - $out)) }}</p></div>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap gap-3">
        <select name="type" onchange="this.form.submit()" class="rounded-xl border-line bg-white py-2.5 text-sm"><option value="">Income and expenses</option><option value="income" @selected(request('type') === 'income')>Income only</option><option value="expense" @selected(request('type') === 'expense')>Expenses only</option></select>
        <input type="month" name="month" value="{{ request('month') }}" onchange="this.form.submit()" class="rounded-xl border-line bg-white px-4 py-2 text-sm" aria-label="Month">
        @if (request()->hasAny(['type', 'month'])) <a href="{{ route('finance.transactions') }}" class="px-2 py-2.5 text-sm text-ink-soft hover:text-ink">Clear</a> @endif
    </form>

    <div class="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[620px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-5 py-3 font-medium">Transaction</th><th class="px-5 py-3 font-medium">Date</th><th class="px-5 py-3 font-medium">Category</th><th class="px-5 py-3 font-medium">Amount</th><th class="px-5 py-3 font-medium">Status</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($rows as $r)
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5 font-semibold">{{ $r['ref'] }}</td>
                        <td class="px-5 py-3.5">{{ $r['date']->format('j M Y') }}</td>
                        <td class="px-5 py-3.5">{{ $r['category'] }}</td>
                        <td class="px-5 py-3.5 font-medium">{{ $r['amount'] < 0 ? '−' : '+' }}${{ number_format(abs($r['amount']), 2) }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$r['status'] === 'Paid' ? 'paid' : 'pending'">{{ $r['status'] }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-ink-soft">No transactions match your filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $rows->links() }}</div>
</x-layouts.finance>

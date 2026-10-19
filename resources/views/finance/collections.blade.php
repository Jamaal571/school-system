<x-layouts.finance title="Fee collections" crumb="Fee collections">
    <div x-data="{ open: {{ request('offline') || $errors->hasAny(['invoice_number']) ? 'true' : 'false' }} }">
        <x-page-header title="Fee collections" subtitle="Unpaid and overdue balances. Record payments and remind parents.">
            <button type="button" @click="open = ! open" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="banknote" class="h-5 w-5" /> Record offline payment</button>
        </x-page-header>

        <form x-show="open" x-transition style="display:none" method="POST" action="{{ route('finance.collections.offline') }}"
              class="mt-5 grid gap-4 rounded-2xl border border-line bg-white p-6 sm:grid-cols-2 xl:grid-cols-4 xl:items-end">
            @csrf
            <x-form-field name="invoice_number" label="Invoice number" placeholder="INV-00012" required />
            <x-form-field name="amount" type="number" step="0.01" label="Amount received ($)" required />
            <label class="block"><span class="text-sm font-medium">Method</span>
                <select name="method" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">@foreach ($methods as $m)<option>{{ $m }}</option>@endforeach</select></label>
            <button class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Record payment</button>
        </form>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by student, admission number or invoice" class="min-w-[240px] flex-1 rounded-xl border-line bg-white px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
        <select name="status" class="rounded-xl border-line bg-white py-2.5 text-sm"><option value="">Overdue and pending</option><option value="overdue" @selected(request('status') === 'overdue')>Overdue only</option><option value="pending" @selected(request('status') === 'pending')>Pending only</option></select>
        <button class="rounded-xl border border-line bg-white px-5 py-2.5 text-sm font-medium hover:bg-wash">Filter</button>
        @if (request()->hasAny(['q', 'status'])) <a href="{{ route('finance.collections') }}" class="px-2 py-2.5 text-sm text-ink-soft hover:text-ink">Clear</a> @endif
    </form>

    @if ($errors->any() && ! $errors->has('invoice_number'))
        <p class="mt-4 rounded-xl bg-danger-soft px-4 py-3 text-sm text-danger">{{ $errors->first() }}</p>
    @endif

    <div class="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[900px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr>
                <th class="px-5 py-3 font-medium">Student</th><th class="px-5 py-3 font-medium">Invoice</th><th class="px-5 py-3 font-medium">Balance</th>
                <th class="px-5 py-3 font-medium">Due</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Record payment</th><th class="px-5 py-3 font-medium">Reminder</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($invoices as $i)
                    @php $balance = $i->balance; $guardian = $i->student->guardians->first(); @endphp
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5"><p class="font-semibold">{{ $i->student->full_name }}</p><p class="text-sm text-ink-soft">{{ $i->student->enrollments->sortByDesc('academic_year_id')->first()?->schoolClass?->name }} · {{ $i->student->admission_no }}</p></td>
                        <td class="px-5 py-3.5">{{ $i->number }}<p class="text-sm text-ink-soft">{{ ucfirst($i->category) }}</p></td>
                        <td class="px-5 py-3.5 font-medium">${{ number_format($balance, 2) }}</td>
                        <td class="px-5 py-3.5">{{ $i->due_on->format('j M Y') }}@if ($i->status === 'overdue')<p class="text-sm text-danger">{{ $i->due_on->diffInDays(today()) }} days overdue</p>@endif</td>
                        <td class="px-5 py-3.5"><x-badge :status="$i->status">{{ ucfirst($i->status) }}</x-badge></td>
                        <td class="px-5 py-3.5">
                            <form method="POST" action="{{ route('finance.payments.store', $i) }}" class="flex items-center gap-2">@csrf
                                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $balance }}" value="{{ $balance }}" class="w-24 rounded-lg border-line px-2.5 py-1.5 text-sm" aria-label="Payment amount">
                                <select name="method" class="rounded-lg border-line py-1.5 text-sm" aria-label="Payment method">@foreach ($methods as $m)<option>{{ $m }}</option>@endforeach</select>
                                <button class="rounded-lg bg-primary px-3 py-1.5 text-sm font-semibold text-white hover:bg-primary-hover">Record</button>
                            </form>
                        </td>
                        <td class="px-5 py-3.5">
                            <form method="POST" action="{{ route('finance.invoices.remind', $i) }}">@csrf
                                <button @disabled(! $guardian?->email) class="inline-flex items-center gap-2 rounded-lg border border-line px-3 py-1.5 text-sm font-medium hover:bg-wash disabled:cursor-not-allowed disabled:opacity-50" @if(! $guardian?->email) title="No guardian email on file" @endif><x-app-icon name="send" class="h-4 w-4" /> Send</button>
                            </form>
                            @if ($i->last_reminded_at)<p class="mt-1 text-xs text-ink-soft">Last sent {{ $i->last_reminded_at->diffForHumans() }}</p>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-ink-soft">No unpaid invoices match. Everything is collected.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $invoices->links() }}</div>
</x-layouts.finance>

<x-layouts.admin title="Finance" crumb="Finance">
    <div x-data="{ open: {{ $errors->hasAny(['admission_no', 'description', 'due_on']) ? 'true' : 'false' }} }">
        <x-page-header title="Finance" subtitle="Invoices, payments and who still owes fees.">
            <button type="button" @click="open = ! open"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">
                <x-app-icon name="wallet" class="h-5 w-5" /> New invoice
            </button>
        </x-page-header>

        <form x-show="open" x-transition style="display:none" method="POST" action="{{ route('admin.finance.invoices.store') }}"
              class="mt-5 grid gap-4 rounded-2xl border border-line bg-white p-6 sm:grid-cols-2 xl:grid-cols-5 xl:items-end">
            @csrf
            <x-form-field name="admission_no" label="Admission number" placeholder="NW00001" required />
            <x-form-field name="description" label="Description" placeholder="Term 1 tuition" />
            <x-form-field name="amount" type="number" step="0.01" label="Amount ($)" required />
            <x-form-field name="due_on" type="date" label="Due date" required />
            <button class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Create invoice</button>
        </form>
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Invoiced', '$'.number_format($invoiced), 'text-ink'],
            ['Collected', '$'.number_format($collected), 'text-success'],
            ['Outstanding', '$'.number_format($outstanding), 'text-warning'],
            ['Overdue', $overdueCount.' · $'.number_format($overdueTotal), 'text-danger'],
        ] as [$label, $value, $color])
            <div class="rounded-2xl border border-line bg-white p-6">
                <p class="text-[15px] text-ink-soft">{{ $label }}</p>
                <p class="mt-3 text-2xl font-semibold {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="mt-6 flex flex-wrap gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by student name or admission number"
               class="min-w-[240px] flex-1 rounded-xl border-line bg-white px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
        <select name="status" class="rounded-xl border-line bg-white py-2.5 text-sm">
            <option value="">All invoices</option>
            @foreach (['overdue', 'pending', 'paid'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="rounded-xl border border-line bg-white px-5 py-2.5 text-sm font-medium hover:bg-wash">Filter</button>
        @if (request()->hasAny(['q', 'status']))
            <a href="{{ route('admin.finance') }}" class="px-2 py-2.5 text-sm text-ink-soft hover:text-ink">Clear</a>
        @endif
    </form>

    @if ($errors->has('amount'))
        <p class="mt-4 rounded-xl bg-danger-soft px-4 py-3 text-sm text-danger">{{ $errors->first('amount') }}</p>
    @endif

    <div class="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[820px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft">
                <tr>
                    <th class="px-5 py-3 font-medium">Invoice</th>
                    <th class="px-5 py-3 font-medium">Student</th>
                    <th class="px-5 py-3 font-medium">Amount</th>
                    <th class="px-5 py-3 font-medium">Due</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Record payment</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($invoices as $i)
                    @php $balance = round((float) $i->amount - (float) $i->payments->sum('amount'), 2); @endphp
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5"><p class="font-semibold">{{ $i->number }}</p><p class="text-sm text-ink-soft">{{ $i->description }}</p></td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('admin.students.show', $i->student) }}" class="hover:text-primary">{{ $i->student->full_name }}</a>
                            <p class="text-sm text-ink-soft">{{ $i->student->admission_no }}</p>
                        </td>
                        <td class="px-5 py-3.5">${{ number_format((float) $i->amount, 2) }}
                            @if ($balance > 0 && $balance < (float) $i->amount)<p class="text-sm text-ink-soft">${{ number_format($balance, 2) }} left</p>@endif</td>
                        <td class="px-5 py-3.5">{{ $i->due_on->format('j M Y') }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$i->status">{{ ucfirst($i->status) }}</x-badge></td>
                        <td class="px-5 py-3.5">
                            @if ($balance > 0)
                                <form method="POST" action="{{ route('admin.finance.payments.store', $i) }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="amount" step="0.01" min="0.01" max="{{ $balance }}" value="{{ $balance }}"
                                           class="w-24 rounded-lg border-line px-2.5 py-1.5 text-sm" aria-label="Payment amount">
                                    <select name="method" class="rounded-lg border-line py-1.5 text-sm" aria-label="Payment method">
                                        <option>cash</option><option>bank transfer</option><option>mobile money</option>
                                    </select>
                                    <button class="rounded-lg bg-primary px-3 py-1.5 text-sm font-semibold text-white hover:bg-primary-hover">Record</button>
                                </form>
                            @else
                                <span class="text-sm text-ink-soft">Fully paid</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-ink-soft">No invoices match your filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $invoices->links() }}</div>
</x-layouts.admin>

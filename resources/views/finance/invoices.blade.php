<x-layouts.finance title="Invoices" crumb="Invoices">
    <div x-data="{ open: {{ request('new') || $errors->hasAny(['admission_no', 'category', 'description', 'amount', 'due_on']) ? 'true' : 'false' }} }">
        <x-page-header title="Invoices" subtitle="Every invoice issued to students.">
            <button type="button" @click="open = ! open" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="invoice" class="h-5 w-5" /> Generate invoice</button>
        </x-page-header>

        <form x-show="open" x-transition style="display:none" method="POST" action="{{ route('finance.invoices.store') }}" class="mt-5 grid gap-4 rounded-2xl border border-line bg-white p-6 sm:grid-cols-2 xl:grid-cols-6 xl:items-end">
            @csrf
            <x-form-field name="admission_no" label="Admission number" placeholder="NW00001" required />
            <label class="block"><span class="text-sm font-medium">Category</span>
                <select name="category" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">@foreach (['tuition', 'transport', 'activities'] as $c)<option value="{{ $c }}" @selected(old('category') === $c)>{{ ucfirst($c) }}</option>@endforeach</select></label>
            <x-form-field name="description" label="Description" placeholder="Term 2 tuition" />
            <x-form-field name="amount" type="number" step="0.01" label="Amount ($)" required />
            <x-form-field name="due_on" type="date" label="Due date" :value="now()->addDays(30)->toDateString()" required />
            <button class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Create invoice</button>
        </form>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by student, admission number or invoice" class="min-w-[240px] flex-1 rounded-xl border-line bg-white px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
        <select name="category" class="rounded-xl border-line bg-white py-2.5 text-sm"><option value="">All categories</option>@foreach (['tuition', 'transport', 'activities'] as $c)<option value="{{ $c }}" @selected(request('category') === $c)>{{ ucfirst($c) }}</option>@endforeach</select>
        <select name="status" class="rounded-xl border-line bg-white py-2.5 text-sm"><option value="">Any status</option>@foreach (['pending', 'overdue', 'paid'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
        <button class="rounded-xl border border-line bg-white px-5 py-2.5 text-sm font-medium hover:bg-wash">Filter</button>
        @if (request()->hasAny(['q', 'status', 'category'])) <a href="{{ route('finance.invoices') }}" class="px-2 py-2.5 text-sm text-ink-soft hover:text-ink">Clear</a> @endif
    </form>

    <div class="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[720px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-5 py-3 font-medium">Invoice</th><th class="px-5 py-3 font-medium">Student</th><th class="px-5 py-3 font-medium">Category</th><th class="px-5 py-3 font-medium">Amount</th><th class="px-5 py-3 font-medium">Paid</th><th class="px-5 py-3 font-medium">Due</th><th class="px-5 py-3 font-medium">Status</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($invoices as $i)
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5 font-semibold">{{ $i->number }}<p class="text-sm font-normal text-ink-soft">{{ $i->description }}</p></td>
                        <td class="px-5 py-3.5">{{ $i->student->full_name }}<p class="text-sm text-ink-soft">{{ $i->student->admission_no }}</p></td>
                        <td class="px-5 py-3.5">{{ ucfirst($i->category) }}</td>
                        <td class="px-5 py-3.5">${{ number_format((float) $i->amount, 2) }}</td>
                        <td class="px-5 py-3.5">${{ number_format((float) $i->payments->sum('amount'), 2) }}</td>
                        <td class="px-5 py-3.5">{{ $i->due_on->format('j M Y') }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$i->status">{{ ucfirst($i->status) }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-ink-soft">No invoices match your filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $invoices->links() }}</div>
</x-layouts.finance>

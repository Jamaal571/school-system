<x-layouts.finance title="Reports" crumb="Reports">
    <x-page-header title="Reports" subtitle="Download financial data as spreadsheet files (CSV)." />
    <div class="mt-6 grid gap-5 md:grid-cols-3">
        <form method="GET" action="{{ route('finance.reports.export', 'statement') }}" class="flex flex-col rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Financial statement</h2><p class="mt-1 flex-1 text-sm text-ink-soft">Revenue, expenses and net for each of the last 12 months.</p>
            <button class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="arrow-down-tray" class="h-5 w-5" /> Download</button>
        </form>
        <form method="GET" action="{{ route('finance.reports.export', 'fees') }}" class="flex flex-col rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Fees</h2><p class="mt-1 text-sm text-ink-soft">Invoices with paid amount and balance.</p>
            <label class="mt-4 block flex-1"><span class="text-sm font-medium">Which invoices</span>
                <select name="status" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm"><option value="">All</option><option value="overdue">Overdue only</option><option value="pending">Pending only</option><option value="paid">Paid only</option></select></label>
            <button class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="arrow-down-tray" class="h-5 w-5" /> Download</button>
        </form>
        <form method="GET" action="{{ route('finance.reports.export', 'transactions') }}" class="flex flex-col rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Transactions</h2><p class="mt-1 text-sm text-ink-soft">All money in and out for the dates you choose.</p>
            <div class="mt-4 grid flex-1 grid-cols-2 gap-3"><x-form-field name="from" type="date" label="From" :value="now()->subDays(30)->toDateString()" /><x-form-field name="to" type="date" label="To" :value="now()->toDateString()" /></div>
            <button class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="arrow-down-tray" class="h-5 w-5" /> Download</button>
        </form>
    </div>
</x-layouts.finance>

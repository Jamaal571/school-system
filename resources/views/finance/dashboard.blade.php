@php
    $money = fn ($v) => '$'.number_format($v);
    $list = fn ($pairs) => collect($pairs)->filter(fn ($v) => $v > 0)->map(fn ($v, $k) => ucfirst($k).' '.$money($v))->implode(' · ') ?: 'None yet';
    $expenseLine = $list(['salaries' => $expenseBy['salaries'] ?? 0, 'supplies' => $expenseBy['supplies'] ?? 0, 'utilities' => $expenseBy['utilities'] ?? 0, 'other' => $otherExpenses]);
    $revenueLine = $list(['tuition' => $revenueBy['tuition'] ?? 0, 'transport' => $revenueBy['transport'] ?? 0, 'activities' => $revenueBy['activities'] ?? 0]);
@endphp
<x-layouts.finance title="Overview" crumb="Overview">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Financial overview</h1>
            <p class="mt-2 text-[15px] text-ink-soft">A clear view of collections, cash flow, and school commitments.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.reports') }}" class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium"><x-app-icon name="calendar" class="h-5 w-5 text-ink-soft" /> {{ $month }}</a>
            <a href="{{ route('finance.invoices', ['new' => 1]) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="invoice" class="h-5 w-5" /> Generate invoice</a>
        </div>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $s)
            <x-stat-card :label="$s['label']" :value="$s['value']" :icon="$s['icon']" :note="$s['note']" :tone="$s['tone']" :arrow="$s['arrow'] ?? false" />
        @endforeach
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-3">
        <p class="text-[15px] font-medium text-ink-soft">Quick actions</p>
        <div class="grid flex-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Generate Invoice', 'invoice', route('finance.invoices', ['new' => 1])], ['Process Payroll', 'users', route('finance.payroll')],
                       ['Record Offline Payment', 'banknote', route('finance.collections', ['offline' => 1])],
                       ['Export Financial Statement', 'arrow-down-tray', route('finance.reports.export', 'statement')]] as [$text, $icon, $href])
                <a href="{{ $href }}" class="flex items-center justify-center gap-3 rounded-xl border border-line bg-white py-4 text-sm font-medium hover:border-primary"><x-app-icon :name="$icon" class="h-5 w-5 text-primary" /> {{ $text }}</a>
            @endforeach
        </div>
    </div>

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-xl font-semibold">Revenue vs. expenses</h2><p class="mt-1 text-sm text-ink-soft">Monthly cash flow · USD · through {{ now()->format('j M') }}</p></div>
                    <a href="{{ route('finance.reports') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Full report <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <p class="text-3xl font-medium">{{ ($net < 0 ? '−' : '').$money(abs($net)) }}</p>
                        <span @class(['rounded-md px-2 py-1 text-sm font-medium', 'bg-success-soft text-success' => $net >= 0, 'bg-danger-soft text-danger' => $net < 0])>Net this month</span>
                    </div>
                    <div class="flex items-center gap-5 text-xs text-ink-soft">
                        <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-primary"></span> Revenue</span>
                        <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-[#A9BEF0]"></span> Expenses</span>
                    </div>
                </div>
                <div class="mt-3"><x-bar-chart :groups="$bars" :top="$top" label="Revenue and expenses for the last three months" /></div>
                <div class="mt-4 grid gap-4 border-t border-line pt-4 text-sm sm:grid-cols-2">
                    <div><p class="text-xs uppercase tracking-wide text-ink-soft">Revenue</p><p class="mt-1 text-body">{{ $revenueLine }}</p></div>
                    <div><p class="text-xs uppercase tracking-wide text-ink-soft">Expenses</p><p class="mt-1 text-body">{{ $expenseLine }}</p></div>
                </div>
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-xl font-semibold">Recent transactions</h2><p class="mt-1 text-sm text-ink-soft">Latest posted entries across all accounts</p></div>
                    <a href="{{ route('finance.transactions') }}" class="flex items-center gap-1 text-sm font-medium text-primary">View ledger <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-[15px]">
                        <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-3 py-3 font-medium">Transaction</th><th class="px-3 py-3 font-medium">Date</th><th class="px-3 py-3 font-medium">Category</th><th class="px-3 py-3 font-medium">Amount</th><th class="px-3 py-3 font-medium">Status</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($recent as $r)
                                <tr>
                                    <td class="px-3 py-3.5 font-semibold">{{ $r['ref'] }}</td>
                                    <td class="px-3 py-3.5">{{ $r['date']->format('j M') }}</td>
                                    <td class="px-3 py-3.5">{{ $r['category'] }}</td>
                                    <td class="px-3 py-3.5 font-medium">{{ $r['amount'] < 0 ? '−' : '+' }}{{ $money(abs($r['amount'])) }}</td>
                                    <td class="px-3 py-3.5"><x-badge :status="$r['status'] === 'Paid' ? 'paid' : 'pending'">{{ $r['status'] }}</x-badge></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-10 text-center text-ink-soft">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="space-y-5">
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-xl font-semibold">Overdue student fees</h2><p class="mt-1 text-sm text-ink-soft">{{ $money($overdueTotal) }} outstanding · Showing {{ $overdue->count() }} of {{ $overdueCount }}</p></div>
                    <a href="{{ route('finance.collections', ['status' => 'overdue']) }}" class="flex items-center gap-1 text-sm font-medium text-primary">View all <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <ul class="mt-4 divide-y divide-line">
                    @forelse ($overdue as $i)
                        <li class="py-4 first:pt-0 last:pb-0">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $i->student->full_name }}</p>
                                    <p class="text-sm text-ink-soft">{{ $i->student->enrollments->sortByDesc('academic_year_id')->first()?->schoolClass?->name }} · {{ $i->number }} · {{ $i->due_on->diffInDays(today()) }} days overdue</p>
                                </div>
                                <p class="font-semibold text-danger">{{ $money($i->balance) }}</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <p class="text-sm text-ink-soft">Due {{ $i->due_on->format('j M Y') }}</p>
                                <form method="POST" action="{{ route('finance.invoices.remind', $i) }}">@csrf
                                    <button class="inline-flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-sm font-medium hover:bg-wash"><x-app-icon name="send" class="h-4 w-4" /> Send reminder</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-8 text-center text-sm text-ink-soft">No overdue fees. Well done.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <h2 class="text-xl font-semibold">Budget requests</h2>
                <p class="mt-1 text-sm text-ink-soft">Awaiting head of school approval</p>
                <ul class="mt-4 divide-y divide-line">
                    @forelse ($budgets as $b)
                        <li class="flex gap-4 py-4 first:pt-0 last:pb-0">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-soft text-warning"><x-app-icon name="wallet" class="h-5 w-5" /></span>
                            <div>
                                <p class="font-semibold">{{ $b->title }} · {{ $money((float) $b->amount) }}</p>
                                <p class="text-sm text-ink-soft">Submitted {{ $b->created_at->isToday() ? 'today' : $b->created_at->format('j M') }} · {{ $b->department }}</p>
                                <a href="{{ route('finance.budgets') }}" class="mt-1 inline-block text-sm font-medium text-primary hover:underline">View request →</a>
                            </div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-ink-soft">No requests waiting. <a href="{{ route('finance.budgets') }}" class="font-medium text-primary">Create one</a></li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>

    <footer class="mt-8 flex flex-wrap justify-between gap-2 text-sm text-ink-soft"><span>Northwood Academy · Finance workspace</span><span>Last updated today at {{ now()->format('g:i A') }}</span></footer>
</x-layouts.finance>

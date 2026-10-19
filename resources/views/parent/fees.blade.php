<x-layouts.parent title="Fees & payments" crumb="Fees & payments">
    <x-page-header title="Fees &amp; payments" :subtitle="$child->full_name.' · invoices and payment history'" />
    <div class="mt-6"><x-child-switcher :children="$children" :child="$child" /></div>

    <div class="overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[680px] text-left text-[15px]">
            <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-5 py-3 font-medium">Invoice</th><th class="px-5 py-3 font-medium">Amount</th><th class="px-5 py-3 font-medium">Balance</th><th class="px-5 py-3 font-medium">Due</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 text-right font-medium">Actions</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($invoices as $i)
                    <tr class="hover:bg-wash">
                        <td class="px-5 py-3.5 font-semibold">{{ $i->number }}<p class="text-sm font-normal text-ink-soft">{{ $i->description ?: ucfirst($i->category) }}</p></td>
                        <td class="px-5 py-3.5">${{ number_format((float) $i->amount, 2) }}</td>
                        <td class="px-5 py-3.5 font-medium">${{ number_format($i->balance, 2) }}</td>
                        <td class="px-5 py-3.5">{{ $i->due_on->format('j M Y') }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$i->status">{{ ucfirst($i->status) }}</x-badge></td>
                        <td class="px-5 py-3.5 text-right text-sm font-medium"><a href="{{ route('parent.invoices.show', $i) }}" class="text-primary hover:underline">View</a>@if ($i->balance > 0)<a href="{{ route('parent.invoices.pay', $i) }}" class="ml-4 text-primary hover:underline">How to pay</a>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-ink-soft">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-8 text-xl font-semibold">Payment history</h2>
    <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-white">
        <ul class="divide-y divide-line">
            @forelse ($payments as $p)
                <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5"><div><p class="font-medium">${{ number_format((float) $p->amount, 2) }} · {{ ucfirst($p->method) }}</p><p class="text-sm text-ink-soft">{{ \Carbon\Carbon::parse($p->paid_on)->format('j M Y') }} · {{ $p->invoice->number }}</p></div>
                    <a href="{{ route('parent.receipts.show', $p) }}" class="text-sm font-medium text-primary hover:underline">Receipt</a></li>
            @empty
                <li class="px-5 py-12 text-center text-ink-soft">No payments recorded yet.</li>
            @endforelse
        </ul>
    </div>
</x-layouts.parent>

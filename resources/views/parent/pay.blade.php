<x-layouts.parent title="How to pay" crumb="Fees & payments">
    <x-page-header :title="'Pay '.$invoice->number" :subtitle="$invoice->student->full_name.' · balance $'.number_format($invoice->balance, 2)">
        <a href="{{ route('parent.fees') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Back</a>
    </x-page-header>
    <section class="mt-6 max-w-2xl rounded-2xl border border-line bg-white p-6">
        <p class="rounded-xl bg-primary-soft px-4 py-3 text-sm text-body">Online card payment is not connected yet. Pay at the school finance office, or by bank transfer, and quote the reference below. The finance team records your payment and your balance updates here straight away.</p>
        <dl class="mt-5 space-y-3 text-[15px]">
            <div class="flex justify-between"><dt class="text-ink-soft">Reference to quote</dt><dd class="font-semibold">{{ $invoice->number }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Amount to pay</dt><dd class="font-semibold">${{ number_format($invoice->balance, 2) }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Due date</dt><dd>{{ $invoice->due_on->format('j F Y') }}</dd></div>
        </dl>
        <p class="mt-5 text-sm text-ink-soft">For bank details, ask the school finance office.</p>
        <a href="{{ route('parent.invoices.show', $invoice) }}" class="mt-5 inline-block rounded-xl border border-line px-5 py-2.5 text-sm font-medium hover:bg-wash">View invoice</a>
    </section>
</x-layouts.parent>

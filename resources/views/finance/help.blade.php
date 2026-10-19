@php
    $guides = [
        ['How do I record a payment?', 'Open Fee collections, type the amount next to the invoice and press Record. For a payment received outside the system, press Record offline payment and enter the invoice number.'],
        ['How do I remind a parent?', 'Press Send next to the invoice in Fee collections, or Send reminder on the dashboard. It emails the guardian. The button is disabled when no guardian email is on file.'],
        ['How do I process payroll?', 'Open Payroll, check the monthly salaries, choose the month and press Process payroll. Each month can only be processed once.'],
        ['How do I ask for a budget?', 'Open Budgets and fill in New budget request. The head of school approves it under Approvals.'],
        ['How do I export the statement?', 'Open Reports and press Download on Financial statement. It opens in Excel or Google Sheets.'],
    ];
@endphp
<x-layouts.finance title="Help & support" crumb="Help & support">
    <x-page-header title="Help & support" subtitle="Quick answers for everyday tasks." />
    <div class="mt-6 max-w-3xl space-y-3" x-data="{ open: 0 }">
        @foreach ($guides as $i => [$q, $a])
            <div class="rounded-2xl border border-line bg-white">
                <button type="button" @click="open = open === {{ $i + 1 }} ? 0 : {{ $i + 1 }}" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">{{ $q }}<x-app-icon name="chevron-right" class="h-5 w-5 text-ink-soft" /></button>
                <p x-show="open === {{ $i + 1 }}" x-transition style="display:none" class="px-6 pb-5 text-[15px] text-body">{{ $a }}</p>
            </div>
        @endforeach
        <p class="pt-3 text-sm text-ink-soft">Cannot find your answer? Ask the school admin.</p>
    </div>
</x-layouts.finance>

@php
    $guides = [
        ['How do I see my child’s grades?', 'Open Academic progress. Each subject shows an average and the latest assessment. Press Full report on the dashboard for every assignment.'],
        ['How do I write to a teacher?', 'Press Message a teacher, choose the teacher, and write your message. Replies appear under Communications.'],
        ['How do I pay fees?', 'Open Fees & payments and press Pay. Online card payment is not connected yet, so pay at the finance office or by bank transfer using the invoice number. Your balance updates once it is recorded.'],
        ['Where is my receipt?', 'Open Fees & payments, then press Receipt next to the payment. You can print it or save it as a PDF.'],
        ['I have more than one child. How do I switch?', 'Use the child buttons at the top of each page, or open My children.'],
    ];
@endphp
<x-layouts.parent title="Help & support" crumb="Help & support">
    <x-page-header title="Help &amp; support" subtitle="Quick answers for parents." />
    <div class="mt-6 max-w-3xl space-y-3" x-data="{ open: 0 }">
        @foreach ($guides as $i => [$q, $a])
            <div class="rounded-2xl border border-line bg-white"><button type="button" @click="open = open === {{ $i + 1 }} ? 0 : {{ $i + 1 }}" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">{{ $q }}<x-app-icon name="chevron-right" class="h-5 w-5 text-ink-soft" /></button>
                <p x-show="open === {{ $i + 1 }}" x-transition style="display:none" class="px-6 pb-5 text-[15px] text-body">{{ $a }}</p></div>
        @endforeach
        <p class="pt-3 text-sm text-ink-soft">Cannot find your answer? Contact the school office.</p>
    </div>
</x-layouts.parent>

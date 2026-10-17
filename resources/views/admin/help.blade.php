@php
    $guides = [
        ['How do I take attendance?', 'Open Attendance, choose the class and the date, mark each student, then press Save attendance. You can come back and change it the same day.'],
        ['How do I add a student?', 'Open Students, press Add student, fill in the details and choose a class. The admission number is created automatically.'],
        ['How do I record a fee payment?', 'Open Finance, find the invoice, type the amount you received and press Record. The invoice turns to Paid when the full amount is received.'],
        ['How do I approve a request?', 'Open Approvals. Urgent requests are at the top. Press Approve or Reject on the request.'],
        ['How do I give someone a login?', 'Use Add New User on the dashboard. The role you choose decides which area they can open.'],
        ['How do I download a report?', 'Open Reports and press Download on the report you need. It opens in Excel or Google Sheets.'],
    ];
@endphp
<x-layouts.admin title="Help & support" crumb="Help & support">
    <x-page-header title="Help & support" subtitle="Quick answers for everyday tasks." />

    <div class="mt-6 max-w-3xl space-y-3" x-data="{ open: 0 }">
        @foreach ($guides as $i => [$question, $answer])
            <div class="rounded-2xl border border-line bg-white">
                <button type="button" @click="open = open === {{ $i + 1 }} ? 0 : {{ $i + 1 }}" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                    {{ $question }}
                    <x-app-icon name="chevron-right" class="h-5 w-5 text-ink-soft transition" />
                </button>
                <p x-show="open === {{ $i + 1 }}" x-transition style="display:none" class="px-6 pb-5 text-[15px] text-body">{{ $answer }}</p>
            </div>
        @endforeach
        <p class="pt-3 text-sm text-ink-soft">Cannot find your answer? Ask the person who set up this system for your school.</p>
    </div>
</x-layouts.admin>

@php
    $isReceipt = isset($payment);
    $inv = $isReceipt ? $payment->invoice : $invoice;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isReceipt ? 'Receipt RCT-'.str_pad($payment->id, 5, '0', STR_PAD_LEFT) : 'Invoice '.$inv->number }} · Northwood Academy</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas font-sans text-ink antialiased print:bg-white">
    <main class="mx-auto my-8 max-w-2xl rounded-2xl border border-line bg-white p-8 print:my-0 print:border-0">
        <div class="flex items-start justify-between">
            <div><p class="text-xl font-bold">Northwood Academy</p><p class="text-sm text-ink-soft">Finance office</p></div>
            <div class="text-right"><p class="text-lg font-semibold">{{ $isReceipt ? 'Receipt' : 'Invoice' }}</p><p class="text-sm text-ink-soft">{{ $isReceipt ? 'RCT-'.str_pad($payment->id, 5, '0', STR_PAD_LEFT) : $inv->number }}</p></div>
        </div>
        <dl class="mt-8 grid gap-4 text-[15px] sm:grid-cols-2">
            <div><dt class="text-sm text-ink-soft">Student</dt><dd class="font-semibold">{{ $inv->student->full_name }}</dd><dd class="text-sm text-ink-soft">{{ $inv->student->admission_no }}</dd></div>
            <div><dt class="text-sm text-ink-soft">{{ $isReceipt ? 'Payment date' : 'Due date' }}</dt><dd class="font-semibold">{{ $isReceipt ? \Carbon\Carbon::parse($payment->paid_on)->format('j F Y') : $inv->due_on->format('j F Y') }}</dd></div>
            <div><dt class="text-sm text-ink-soft">For</dt><dd>{{ $inv->description ?: ucfirst($inv->category) }}</dd><dd class="text-sm text-ink-soft">Invoice {{ $inv->number }}</dd></div>
            @if ($isReceipt)<div><dt class="text-sm text-ink-soft">Method</dt><dd>{{ ucfirst($payment->method) }}</dd></div>@endif
        </dl>
        <table class="mt-8 w-full text-left text-[15px]">
            @if ($isReceipt)
                <tr class="border-t border-line"><td class="py-3 text-ink-soft">Amount received</td><td class="py-3 text-right text-2xl font-semibold">${{ number_format((float) $payment->amount, 2) }}</td></tr>
                <tr class="border-t border-line"><td class="py-3 text-ink-soft">Remaining balance on {{ $inv->number }}</td><td class="py-3 text-right">${{ number_format($inv->load('payments')->balance, 2) }}</td></tr>
            @else
                <tr class="border-t border-line"><td class="py-3 text-ink-soft">Total</td><td class="py-3 text-right font-semibold">${{ number_format((float) $inv->amount, 2) }}</td></tr>
                @foreach ($inv->payments as $p)<tr class="border-t border-line"><td class="py-3 text-ink-soft">Payment {{ \Carbon\Carbon::parse($p->paid_on)->format('j M Y') }} · {{ ucfirst($p->method) }}</td><td class="py-3 text-right">−${{ number_format((float) $p->amount, 2) }}</td></tr>@endforeach
                <tr class="border-t border-line"><td class="py-3 font-semibold">Balance due</td><td class="py-3 text-right text-2xl font-semibold">${{ number_format($inv->balance, 2) }}</td></tr>
            @endif
        </table>
        <div class="mt-8 flex gap-3 print:hidden">
            <button onclick="window.print()" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover">Print or save as PDF</button>
            <a href="{{ route('parent.fees') }}" class="rounded-xl border border-line px-5 py-2.5 text-sm font-medium hover:bg-wash">Back to fees</a>
        </div>
    </main>
</body>
</html>

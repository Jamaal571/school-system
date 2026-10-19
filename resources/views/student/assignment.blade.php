<x-layouts.student :title="$assignment->title" crumb="Assignments">
    <x-page-header :title="$assignment->title" :subtitle="$assignment->subject->name.' · '.($assignment->teacher?->full_name ?? 'Teacher')">
        <a href="{{ route('student.assignments') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Back</a>
    </x-page-header>
    <section class="mt-6 max-w-2xl rounded-2xl border border-line bg-white p-6">
        <dl class="space-y-3 text-[15px]">
            <div class="flex justify-between"><dt class="text-ink-soft">Status</dt><dd><x-badge :status="$status[1]">{{ $status[0] }}</x-badge></dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Due</dt><dd>{{ $assignment->due_on->format('l, j F Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Worth</dt><dd>{{ $assignment->max_score }} points</dd></div>
            @if ($sub?->submitted_on)<div class="flex justify-between"><dt class="text-ink-soft">Handed in</dt><dd>{{ $sub->submitted_on->format('j M Y') }}</dd></div>@endif
            @if ($sub && $sub->score !== null)<div class="flex justify-between"><dt class="text-ink-soft">Your score</dt><dd class="text-lg font-semibold">{{ rtrim(rtrim(number_format((float) $sub->score, 1), '0'), '.') }} / {{ $assignment->max_score }}</dd></div>@endif
        </dl>
        @if ($sub?->feedback)<div class="mt-5 rounded-xl bg-primary-soft px-4 py-3"><p class="text-sm font-semibold">Teacher feedback</p><p class="mt-1 text-[15px] text-body">“{{ $sub->feedback }}”</p></div>@endif
        <p class="mt-5 text-sm text-ink-soft">Hand your work to your teacher. They record it here and add your score.</p>
        <a href="{{ route('student.messages.create') }}" class="mt-4 inline-block rounded-xl border border-line px-5 py-2.5 text-sm font-medium hover:bg-wash">Ask your teacher a question</a>
    </section>
</x-layouts.student>

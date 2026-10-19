@php
    $guides = [
        ['Where do I see my deadlines?', 'Open Assignments. The To do tab shows what you still need to hand in, soonest first.'],
        ['How do I hand in my work?', 'Give your work to your teacher. They record it, and the status changes to Handed in. Online uploads are not available yet.'],
        ['Where are my grades and feedback?', 'Open Grades & progress for your subject averages and every graded assignment with your teacher’s feedback.'],
        ['How do I ask my teacher something?', 'Open Communications and press New message, or press Ask your teacher a question on an assignment.'],
        ['Where is my timetable?', 'Open Timetable for the whole week. The dashboard shows today’s lessons and what is next.'],
    ];
@endphp
<x-layouts.student title="Help & support" crumb="Help & support">
    <x-page-header title="Help &amp; support" subtitle="Quick answers for students." />
    <div class="mt-6 max-w-3xl space-y-3" x-data="{ open: 0 }">
        @foreach ($guides as $i => [$q, $a])
            <div class="rounded-2xl border border-line bg-white"><button type="button" @click="open = open === {{ $i + 1 }} ? 0 : {{ $i + 1 }}" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">{{ $q }}<x-app-icon name="chevron-right" class="h-5 w-5 text-ink-soft" /></button>
                <p x-show="open === {{ $i + 1 }}" x-transition style="display:none" class="px-6 pb-5 text-[15px] text-body">{{ $a }}</p></div>
        @endforeach
        <p class="pt-3 text-sm text-ink-soft">Cannot find your answer? Ask your teacher or the school office.</p>
    </div>
</x-layouts.student>

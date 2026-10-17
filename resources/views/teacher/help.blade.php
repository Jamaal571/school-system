@php
    $guides = [
        ['How do I take attendance?', 'Open Attendance, choose the class and date, mark each student, then press Save attendance. From the dashboard, press Take attendance on your next class.'],
        ['How do I create an assignment?', 'Open Assignments and press Create assignment. Choose the class, the due date and how many points it is out of.'],
        ['How do I enter scores?', 'In Assignments, press Grade next to the assignment, type each score, and press Save scores. You can come back and change them later.'],
        ['Where do I see how a class is doing?', 'Open Gradebook and choose the class. Students averaging below 60% are shown in red.'],
    ];
@endphp
<x-layouts.teacher title="Help & support" crumb="Help & support">
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
</x-layouts.teacher>

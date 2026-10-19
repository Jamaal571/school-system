@php
    $first = $me->first_name;
    $rel = fn ($d) => $d->isToday() ? 'today' : ($d->isTomorrow() ? 'tomorrow' : $d->format('D j M'));
    $next_due = $upcoming->first();
    $minutes = $next && $isToday ? now()->diffInMinutes($next['start'], false) : null;
@endphp
<x-layouts.student title="Overview" crumb="Overview">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Keep up the great work, {{ $first }}.</h1>
            <p class="mt-2 text-[15px] text-ink-soft">{{ $class?->name }} · Your learning, deadlines, and school day in one place.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('student.timetable') }}" class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium"><x-app-icon name="calendar" class="h-5 w-5 text-ink-soft" /> {{ now()->format('D · j F') }}</a>
            <a href="{{ route('student.timetable') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="calendar" class="h-5 w-5" /> View my timetable</a>
        </div>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Assignments due" :value="$upcoming->count()" icon="clipboard-document-check" :tone="$upcoming->isEmpty() ? 'success' : 'warning'"
            :note="$next_due ? 'Next deadline: '.$rel($next_due->due_on) : 'Nothing due. Well done.'" />
        <x-stat-card label="Overall average" :value="$average === null ? '—' : $average.'%'" icon="presentation-chart-line"
            :note="$trend === null ? 'From graded work' : abs($trend).' pts since last assessment'" :tone="$trend === null ? 'muted' : ($trend >= 0 ? 'success' : 'danger')" :arrow="$trend === null ? false : ($trend >= 0 ? true : 'down')" />
        <x-stat-card label="Term attendance" :value="$rate === null ? '—' : $rate.'%'" icon="calendar-days" tone="primary" :note="$recorded ? $attended.' of '.$recorded.' school days attended' : 'No attendance recorded yet'" />
        <x-stat-card label="Classes today" :value="$schedule->count()" icon="book-open" tone="muted"
            :note="$isToday ? $complete.' complete · '.($schedule->count() - $complete).' still to come' : 'Showing '.$day->format('l')" />
    </div>

    @if ($next)
        <section class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-primary-soft px-6 py-5">
            <div class="flex items-center gap-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-primary"><x-app-icon name="book-open" class="h-6 w-6" /></span>
                <div>
                    <p class="font-semibold">{{ $isToday ? 'Up next' : 'Next school day, '.$day->format('l') }}: {{ $next['subject'] }} · {{ $next['start']->format('H:i') }}–{{ $next['end']->format('H:i') }}</p>
                    <p class="text-sm text-ink-soft">{{ $next['topic'] }}@if ($next['teacher']) with {{ $next['teacher'] }}@endif · Room {{ $next['room'] }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                @if ($minutes !== null && $minutes >= 0 && $minutes <= 90)<span class="text-sm font-medium text-primary">Starts in {{ $minutes }} min</span>@endif
                <a href="{{ route('student.courses.show', $next['subject_id']) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="folder" class="h-5 w-5" /> Open course</a>
            </div>
        </section>
    @endif

    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-xl font-semibold">Assignments to focus on</h2><p class="mt-1 text-sm text-ink-soft">{{ $upcoming->count() }} upcoming {{ \Illuminate\Support\Str::plural('deadline', $upcoming->count()) }} · Sorted by due date</p></div>
                    <a href="{{ route('student.assignments') }}" class="flex items-center gap-1 text-sm font-medium text-primary">View all <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-[15px]">
                        <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-3 py-3 font-medium">Assignment</th><th class="px-3 py-3 font-medium">Subject</th><th class="px-3 py-3 font-medium">Due date</th><th class="px-3 py-3 font-medium">Status</th><th class="px-3 py-3 font-medium">Action</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($focus as $f)
                                <tr><td class="px-3 py-3.5 font-semibold">{{ $f['a']->title }}</td><td class="px-3 py-3.5">{{ $f['a']->subject->name }}</td><td class="px-3 py-3.5">{{ $f['a']->due_on->format('j M') }}</td>
                                    <td class="px-3 py-3.5"><x-badge :status="$f['status'][1]">{{ $f['status'][0] }}</x-badge></td>
                                    <td class="px-3 py-3.5"><a href="{{ route('student.assignments.show', $f['a']) }}" class="text-sm font-medium text-primary hover:underline">Open</a></td></tr>
                            @empty
                                <tr><td colspan="5" class="py-10 text-center text-ink-soft">Nothing to hand in right now.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($lastWeek > 0)
                    @if ($missed === 0)
                        <p class="mt-4 flex items-center gap-2 rounded-xl bg-success-soft px-4 py-3 text-sm text-success"><x-app-icon name="check-circle" class="h-5 w-5" /> All {{ $lastWeek }} {{ \Illuminate\Support\Str::plural('assignment', $lastWeek) }} from last week were handed in on time. Nice work!</p>
                    @else
                        <p class="mt-4 flex items-center gap-2 rounded-xl bg-warning-soft px-4 py-3 text-sm text-warning"><x-app-icon name="shield-exclamation" class="h-5 w-5" /> {{ $missed }} of {{ $lastWeek }} assignments from last week were not handed in. Talk to your teacher.</p>
                    @endif
                @endif
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-xl font-semibold">My academic progress</h2><p class="mt-1 text-sm text-ink-soft">Latest subject averages</p></div>
                    <a href="{{ route('student.grades') }}" class="flex items-center gap-1 text-sm font-medium text-primary">View report card <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <div class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                    @forelse ($subjects as $s)
                        <div><div class="flex justify-between text-sm"><span class="font-medium">{{ $s['subject'] }}</span><span class="font-semibold">{{ $s['avg'] }}% · {{ $s['letter'] }}</span></div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-canvas" role="progressbar" aria-valuenow="{{ $s['avg'] }}" aria-valuemin="0" aria-valuemax="100"><div class="h-full rounded-full bg-primary" style="width: {{ $s['avg'] }}%"></div></div></div>
                    @empty
                        <p class="py-6 text-center text-sm text-ink-soft sm:col-span-2">Averages appear after your first graded assignment.</p>
                    @endforelse
                </div>
                @if ($feedback)
                    <div class="mt-5 flex gap-4 border-t border-line pt-5"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><x-app-icon name="chat-bubble-left-right" class="h-5 w-5" /></span>
                        <div><p class="font-semibold">Latest feedback · {{ $feedback['title'] }}</p><p class="mt-1 text-sm text-body">“{{ $feedback['feedback'] }}”</p>
                            <p class="mt-1 text-xs text-ink-soft">{{ $feedback['teacher'] }} · Score: {{ rtrim(rtrim(number_format($feedback['score'], 1), '0'), '.') }}/{{ $feedback['max'] }}</p></div></div>
                @endif
            </section>

            @if ($event)
                <section class="rounded-2xl bg-primary-soft p-6"><h2 class="flex items-center gap-2 font-semibold"><x-app-icon name="calendar" class="h-5 w-5 text-primary" /> Your next school event</h2>
                    <p class="mt-2 text-sm text-body">{{ $event->title }} · {{ $event->starts_on->format('l, j F') }}@if ($event->time) · {{ $event->time }}@endif @if ($event->location) · {{ $event->location }}@endif @if ($event->description). {{ $event->description }}@endif</p></section>
            @endif
        </div>

        <div class="space-y-5">
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-xl font-semibold">{{ $isToday ? 'Today’s timetable' : $day->format('l').'’s timetable' }}</h2><p class="mt-1 text-sm text-ink-soft">{{ $day->format('l, j F') }} · {{ $class?->name }}</p></div>
                    <a href="{{ route('student.timetable') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Week view <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <ul class="mt-4 space-y-2">
                    @forelse ($schedule as $r)
                        <li @class(['flex items-center gap-4 rounded-xl px-3 py-3', 'bg-primary-soft' => $r['status'] === 'next'])>
                            <span class="w-11 text-sm {{ $r['status'] === 'next' ? 'font-semibold text-primary' : 'text-ink-soft' }}">{{ $r['start']->format('H:i') }}</span>
                            <span @class(['h-9 w-0.5 rounded', 'bg-primary' => $r['status'] === 'next', 'bg-line' => $r['status'] !== 'next'])></span>
                            <div class="min-w-0 flex-1"><p class="font-semibold">{{ $r['subject'] }}</p><p class="text-xs text-ink-soft">{{ $r['room'] }}@if ($r['teacher']) · {{ $r['teacher'] }}@endif</p></div>
                            @if ($r['status'] === 'complete')<x-app-icon name="check-circle" class="h-5 w-5 text-success" />@elseif ($r['status'] === 'next')<span class="text-sm font-medium text-primary">Next</span>@else<x-app-icon name="clock" class="h-5 w-5 text-ink-soft" />@endif
                        </li>
                    @empty
                        <li class="py-8 text-center text-sm text-ink-soft">No classes in your timetable for this day.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between"><h2 class="text-xl font-semibold">Messages &amp; announcements</h2><a href="{{ route('student.messages') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Inbox <x-app-icon name="arrow-right" class="h-4 w-4" /></a></div>
                <ul class="mt-4 divide-y divide-line">
                    @forelse ($feed as $f)
                        <li class="py-4 first:pt-0 last:pb-0"><a href="{{ $f['link'] }}" class="flex gap-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><x-app-icon :name="$f['type'] === 'message' ? 'chat-bubble-left-right' : 'megaphone'" class="h-5 w-5" /></span>
                            <div class="min-w-0"><p class="font-semibold">{{ $f['title'] }}</p><p class="mt-1 text-sm text-ink-soft">{{ \Illuminate\Support\Str::limit($f['text'], 90) }}</p><p class="mt-1 text-xs text-ink-soft">{{ $f['meta'] }}</p></div></a></li>
                    @empty
                        <li class="py-6 text-center text-sm text-ink-soft">No messages or announcements yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>

    <footer class="mt-8 flex flex-wrap justify-between gap-2 text-sm text-ink-soft"><span>Northwood Academy · Student workspace</span><span>Last updated today at {{ now()->format('g:i A') }}</span></footer>
</x-layouts.student>

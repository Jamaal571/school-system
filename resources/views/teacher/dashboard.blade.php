<x-layouts.teacher title="Overview" crumb="Overview">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Ready for a great school day?</h1>
            <p class="mt-2 text-[15px] text-ink-soft">{{ $greeting }}, {{ $me->first_name }}. {{ $nextLine }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.timetable') }}" class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium">
                <x-app-icon name="calendar" class="h-5 w-5 text-ink-soft" /> {{ now()->format('D · j F') }}
            </a>
            <a href="{{ route('teacher.assignments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">
                <x-app-icon name="plus" class="h-5 w-5" /> Create assignment
            </a>
        </div>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $s)
            <x-stat-card :label="$s['label']" :value="$s['value']" :icon="$s['icon']" :note="$s['note']" :tone="$s['tone']" :arrow="$s['arrow'] ?? false" />
        @endforeach
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-3">
        <p class="text-[15px] font-medium text-ink-soft">Quick actions</p>
        <div class="grid flex-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Log Attendance', 'calendar-days', 'teacher.attendance'], ['Open Gradebook', 'book-open', 'teacher.gradebook'],
                       ['Create Assignment', 'plus', 'teacher.assignments.create'], ['View Timetable', 'calendar', 'teacher.timetable']] as [$text, $icon, $route])
                <a href="{{ route($route) }}" class="flex items-center justify-center gap-3 rounded-xl border border-line bg-white py-4 text-sm font-medium hover:border-primary">
                    <x-app-icon :name="$icon" class="h-5 w-5 text-primary" /> {{ $text }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">{{ $isToday ? 'Today’s teaching schedule' : 'Schedule for '.$day->format('l') }}</h2>
                        <p class="mt-1 text-sm text-ink-soft">{{ $day->format('l, j F') }} · {{ $schedule->count() }} {{ \Illuminate\Support\Str::plural('class', $schedule->count()) }}</p>
                    </div>
                    <a href="{{ route('teacher.timetable') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Full timetable <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($schedule as $row)
                        <div @class(['flex flex-wrap items-center gap-4 rounded-xl px-4 py-4', 'bg-primary-soft' => $row['status'] === 'next', 'bg-wash' => $row['status'] !== 'next'])>
                            <span class="w-12 text-sm text-ink-soft">{{ $row['time'] }}</span>
                            <span class="h-10 w-0.5 rounded bg-primary/40"></span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold">{{ $row['class'] }} · {{ $row['subject'] }}</p>
                                <p class="text-sm text-ink-soft">{{ $row['topic'] }} · Room {{ $row['room'] }} · {{ $row['students'] }} students</p>
                            </div>
                            @if ($row['status'] === 'next')
                                <span class="text-sm font-medium text-primary">Up next</span>
                                <a href="{{ route('teacher.attendance', ['class' => $row['class_id'], 'date' => now()->toDateString()]) }}"
                                   class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-hover">Take attendance</a>
                            @else
                                <span class="text-sm text-ink-soft">{{ $row['status'] === 'complete' ? 'Complete' : 'Upcoming' }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="rounded-xl bg-wash py-10 text-center text-sm text-ink-soft">No classes in your timetable for this day.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">Grading queue</h2>
                        <p class="mt-1 text-sm text-ink-soft">Prioritized by submission date</p>
                    </div>
                    <a href="{{ route('teacher.assignments') }}" class="flex items-center gap-1 text-sm font-medium text-primary">View assignments <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-[15px]">
                        <thead class="text-xs uppercase tracking-wide text-ink-soft">
                            <tr class="border-b border-line"><th class="py-3 pr-4 font-medium">Assignment</th><th class="py-3 pr-4 font-medium">Class</th><th class="py-3 pr-4 font-medium">To grade</th><th class="py-3 pr-4 font-medium">Submitted</th><th class="py-3 text-right font-medium">Action</th></tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($queue as $a)
                                <tr>
                                    <td class="py-4 pr-4 font-semibold">{{ $a->title }}</td>
                                    <td class="py-4 pr-4">{{ $a->schoolClass->name }}</td>
                                    <td class="py-4 pr-4">{{ $a->to_grade }}</td>
                                    <td class="py-4 pr-4">{{ \Carbon\Carbon::parse($a->first_submitted)->format('j M') }}</td>
                                    <td class="py-4 text-right"><a href="{{ route('teacher.assignments.grade', $a) }}" class="text-sm font-medium text-primary hover:underline">Grade now</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-10 text-center text-ink-soft">Nothing waiting to be graded. Well done.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($deadline)
                    <p class="mt-4 text-sm text-ink-soft">Next deadline: {{ $deadline->title }} · {{ $deadline->schoolClass->name }} · {{ $deadline->due_on->isTomorrow() ? 'Tomorrow' : $deadline->due_on->format('D j M') }}</p>
                @endif
            </section>
        </div>

        <div class="space-y-5">
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-xl font-semibold">Class progress</h2><p class="mt-1 text-sm text-ink-soft">Average of graded work</p></div>
                    <a href="{{ route('teacher.gradebook') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Gradebook <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <ul class="mt-5 space-y-4">
                    @forelse ($progress as $p)
                        <li>
                            <div class="flex justify-between text-sm"><span class="font-medium">{{ $p['name'] }}</span><span class="font-semibold">{{ $p['pct'] }}%</span></div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-canvas" role="progressbar" aria-valuenow="{{ $p['pct'] }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="h-full rounded-full bg-primary" style="width: {{ $p['pct'] }}%"></div>
                            </div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-ink-soft">Averages appear after you grade the first assignment.</li>
                    @endforelse
                </ul>
                @if ($needSupport > 0)
                    <p class="mt-5 flex items-center gap-2 text-sm text-warning"><x-app-icon name="shield-exclamation" class="h-4 w-4" /> {{ $needSupport }} {{ \Illuminate\Support\Str::plural('student', $needSupport) }} averaging below 60%</p>
                @endif
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">School announcements</h2>
                    <a href="{{ route('teacher.communications') }}" class="flex items-center gap-1 text-sm font-medium text-primary">All <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <ul class="mt-4 divide-y divide-line">
                    @forelse ($announcements as $n)
                        <li class="py-4 first:pt-0 last:pb-0">
                            <p class="font-semibold">{{ $n->title }}</p>
                            <p class="mt-1 text-sm text-ink-soft">{{ \Illuminate\Support\Str::limit($n->body, 90) }}</p>
                            <p class="mt-1 text-xs text-ink-soft">{{ $n->created_at->diffForHumans() }}</p>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-ink-soft">No announcements yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>

    <footer class="mt-8 flex flex-wrap justify-between gap-2 text-sm text-ink-soft">
        <span>Northwood Academy · Teacher workspace</span><span>Last updated today at {{ now()->format('g:i A') }}</span>
    </footer>
</x-layouts.teacher>

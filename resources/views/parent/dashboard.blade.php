@php
    $money = fn ($v) => '$'.number_format((float) $v, 2);
    $tile = ['present' => ['bg-success-soft text-success', 'check-circle', 'Present'], 'late' => ['bg-warning-soft text-warning', 'clock', 'Late'],
             'absent' => ['bg-danger-soft text-danger', 'shield-exclamation', 'Absent'], 'excused' => ['bg-primary-soft text-primary', 'check-circle', 'Excused'],
             'upcoming' => ['bg-wash text-ink-soft', null, 'Upcoming'], 'none' => ['bg-wash text-ink-soft', null, 'No record']];
    $overdueDays = $primary && $primary->due_on->lt(today()) ? $primary->due_on->diffInDays(today()) : null;
    $nextDue = $upcoming->first();
    $initials = strtoupper(mb_substr($child->first_name, 0, 1).mb_substr($child->last_name, 0, 1));
@endphp
<x-layouts.parent title="Overview" crumb="Overview">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">A closer look at {{ $child->first_name }}’s school day.</h1>
            <p class="mt-2 text-[15px] text-ink-soft">Welcome, {{ explode(' ', auth()->user()->name)[0] }}. Stay connected to your child’s learning and wellbeing.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('parent.calendar') }}" class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium"><x-app-icon name="calendar" class="h-5 w-5 text-ink-soft" /> {{ $term ? $term->name.', '.$term->starts_on->format('Y').'–'.$term->ends_on->format('y') : 'This term' }}</a>
            <a href="{{ route('parent.messages.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="chat-bubble-left-right" class="h-5 w-5" /> Message a teacher</a>
        </div>
    </div>

    <div class="mt-6"><x-child-switcher :children="$children" :child="$child" /></div>

    <section class="-mt-2 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-line bg-white p-5">
        <div class="flex items-center gap-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-soft font-semibold text-primary">{{ $initials }}</span>
            <div>
                <div class="flex flex-wrap items-center gap-3"><h2 class="text-xl font-semibold">{{ $child->full_name }}</h2>@if ($class)<span class="rounded-md bg-primary-soft px-2.5 py-1 text-xs font-semibold text-primary">{{ $class->name }}</span>@endif</div>
                <p class="mt-1 text-sm text-ink-soft">Student ID {{ $child->admission_no }}@if ($class?->homeroomTeacher) · Homeroom: {{ $class->homeroomTeacher->full_name }}@endif @if ($term) · {{ $term->name }}, {{ $term->starts_on->format('Y') }}–{{ $term->ends_on->format('y') }}@endif</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if (in_array($today, ['present', 'late'], true)) <x-badge status="present">At school today</x-badge> @elseif ($today === 'absent') <x-badge status="absent">Absent today</x-badge> @elseif ($today === 'excused') <x-badge status="excused">Excused today</x-badge> @endif
            <a href="{{ route('parent.children.show', $child) }}" class="inline-flex items-center gap-2 rounded-xl border border-line px-4 py-2.5 text-sm font-medium hover:bg-wash"><x-app-icon name="user" class="h-5 w-5" /> View student profile</a>
        </div>
    </section>

    <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Overall average" :value="$average === null ? '—' : $average.'%'" icon="presentation-chart-line"
            :note="$trend === null ? 'From graded work' : abs($trend).' pts since last assessment'" :tone="$trend === null ? 'muted' : ($trend >= 0 ? 'success' : 'danger')" :arrow="$trend === null ? false : ($trend >= 0 ? true : 'down')" />
        <x-stat-card label="Term attendance" :value="$rate === null ? '—' : $rate.'%'" icon="calendar-days" tone="primary"
            :note="($counts['present'] ?? 0).' present'.(($counts['excused'] ?? 0) ? ' · '.$counts['excused'].' excused '.\Illuminate\Support\Str::plural('absence', $counts['excused']) : '').(($counts['absent'] ?? 0) ? ' · '.$counts['absent'].' absent' : '')" />
        <x-stat-card label="Upcoming assignments" :value="$upcoming->count()" icon="clipboard-document-check" tone="muted"
            :note="$nextDue ? 'Next due '.($nextDue->due_on->isToday() ? 'today' : ($nextDue->due_on->isTomorrow() ? 'tomorrow' : $nextDue->due_on->format('j M'))).' · '.$nextDue->subject->name : 'Nothing due'" />
        <x-stat-card label="Outstanding fees" :value="'$'.number_format($outstanding)" icon="wallet" :tone="$outstanding > 0 ? 'warning' : 'success'"
            :note="$primary ? ($primary->description ?: ucfirst($primary->category)).($overdueDays ? ' · '.$overdueDays.' days overdue' : ' · due '.$primary->due_on->format('j M')) : 'All fees paid'" />
    </div>

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between">
                    <div><h2 class="text-xl font-semibold">Academic progress</h2><p class="mt-1 text-sm text-ink-soft">{{ $child->first_name }}’s subject averages{{ $term ? ' · '.$term->name : '' }}</p></div>
                    <a href="{{ route('parent.progress') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Full report <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-[15px]">
                        <thead class="bg-wash text-xs uppercase tracking-wide text-ink-soft"><tr><th class="px-3 py-3 font-medium">Subject</th><th class="px-3 py-3 font-medium">Teacher</th><th class="px-3 py-3 font-medium">Average</th><th class="px-3 py-3 font-medium">Latest assessment</th></tr></thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($subjects as $s)
                                <tr><td class="px-3 py-3.5 font-semibold">{{ $s['subject'] }}</td><td class="px-3 py-3.5">{{ $s['teacher'] ?? '—' }}</td>
                                    <td class="px-3 py-3.5"><span class="rounded-md bg-success-soft px-2.5 py-1 text-sm font-medium text-success">{{ $s['avg'] }}% · {{ $s['letter'] }}</span></td>
                                    <td class="px-3 py-3.5">{{ $s['latest']['title'] }} · {{ rtrim(rtrim(number_format($s['latest']['score'], 1), '0'), '.') }}/{{ $s['latest']['max'] }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-ink-soft">Grades appear here once teachers have marked the first assignment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($nextDue)<p class="mt-4 text-sm text-ink-soft">Next assignment: {{ $nextDue->title }} · Due {{ $nextDue->due_on->format('j M') }}</p>@endif
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between"><h2 class="text-xl font-semibold">Attendance &amp; wellbeing</h2><a href="{{ route('parent.attendance') }}" class="flex items-center gap-1 text-sm font-medium text-primary">View history <x-app-icon name="arrow-right" class="h-4 w-4" /></a></div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-ink-soft">This week · {{ now()->startOfWeek()->format('j') }}–{{ now()->startOfWeek()->addDays(4)->format('j F') }}</p>
                    @if ($today === 'present') <x-badge status="present">Present today</x-badge> @endif</div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
                    @foreach ($week as $d)
                        @php [$cls, $icon, $label] = $tile[$d['status']]; @endphp
                        <div class="rounded-xl px-3 py-4 text-center text-sm {{ $cls }}"><p class="text-xs opacity-80">{{ $d['label'] }}</p><div class="my-2 flex h-5 items-center justify-center">@if ($icon)<x-app-icon :name="$icon" class="h-5 w-5" />@else<span>–</span>@endif</div><p class="font-medium">{{ $label }}</p></div>
                    @endforeach
                </div>
                <p class="mt-4 text-sm text-ink-soft">Term to date: {{ $counts['present'] ?? 0 }} present · {{ $counts['excused'] ?? 0 }} excused · {{ $counts['late'] ?? 0 }} late {{ \Illuminate\Support\Str::plural('arrival', $counts['late'] ?? 0) }}@if ($counts['absent'] ?? 0) · {{ $counts['absent'] }} absent @endif</p>
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between"><h2 class="text-xl font-semibold">Teacher communication</h2><a href="{{ route('parent.messages') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Open inbox <x-app-icon name="arrow-right" class="h-4 w-4" /></a></div>
                <ul class="mt-4 divide-y divide-line">
                    @forelse ($messages as $m)
                        @php $mine = $m->from_user_id === auth()->id(); $other = $mine ? $m->recipient : $m->sender; @endphp
                        <li class="flex gap-4 py-4 first:pt-0 last:pb-0">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><x-app-icon :name="$mine ? 'send' : 'chat-bubble-left-right'" class="h-5 w-5" /></span>
                            <a href="{{ route('parent.messages.show', $other) }}" class="min-w-0"><p class="font-semibold">{{ $mine ? 'Your message to '.$other->name : $other->name }}</p><p class="mt-1 text-sm text-ink-soft">“{{ \Illuminate\Support\Str::limit($m->body, 110) }}”</p><p class="mt-1 text-xs text-ink-soft">{{ $m->created_at->format('j M') }}</p></a>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-ink-soft">No messages yet. <a href="{{ route('parent.messages.create') }}" class="font-medium text-primary">Write to a teacher</a></li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="space-y-5">
            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between"><h2 class="text-xl font-semibold">Fees &amp; payments</h2><a href="{{ route('parent.fees') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Payment history <x-app-icon name="arrow-right" class="h-4 w-4" /></a></div>
                @if ($primary)
                    <div class="mt-4 rounded-xl bg-warning-soft p-5">
                        <div class="flex items-center justify-between text-sm text-warning"><span>Outstanding balance</span>@if ($overdueDays)<span class="font-medium">{{ $overdueDays }} days overdue</span>@endif</div>
                        <p class="mt-3 text-3xl font-semibold">{{ $money($primary->balance) }}</p>
                        <p class="mt-3 text-sm text-warning">{{ $primary->description ?: ucfirst($primary->category) }} · Due {{ $primary->due_on->format('j M Y') }}</p>
                    </div>
                    <dl class="mt-4 space-y-2 text-sm"><div class="flex justify-between"><dt class="text-ink-soft">Invoice</dt><dd class="font-semibold">{{ $primary->number }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-soft">Total {{ $primary->category }} instalment</dt><dd class="font-semibold">{{ $money($primary->amount) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-soft">Payments received</dt><dd class="font-semibold">−{{ $money($primary->payments->sum('amount')) }}</dd></div></dl>
                    <div class="mt-4 flex gap-3">
                        <a href="{{ route('parent.invoices.pay', $primary) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover"><x-app-icon name="wallet" class="h-5 w-5" /> Pay {{ '$'.number_format($primary->balance, 2) }}</a>
                        <a href="{{ route('parent.invoices.show', $primary) }}" class="inline-flex items-center gap-2 rounded-xl border border-line px-4 py-2.5 text-sm font-medium hover:bg-wash"><x-app-icon name="invoice" class="h-5 w-5" /> View invoice</a>
                    </div>
                @else
                    <p class="mt-4 rounded-xl bg-success-soft px-5 py-8 text-center font-medium text-success">All fees are paid. Thank you.</p>
                @endif
                @if ($lastPayment)
                    <div class="mt-5 border-t border-line pt-4 text-sm"><p class="font-medium">Last payment received</p>
                        <p class="mt-1 text-ink-soft">{{ $money($lastPayment->amount) }} · {{ \Carbon\Carbon::parse($lastPayment->paid_on)->format('j M Y') }} · {{ ucfirst($lastPayment->method) }}</p>
                        <a href="{{ route('parent.receipts.show', $lastPayment) }}" class="mt-1 inline-block font-medium text-primary hover:underline">Download receipt →</a></div>
                @endif
            </section>

            <section class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-start justify-between"><h2 class="text-xl font-semibold">Coming up at school</h2><a href="{{ route('parent.calendar') }}" class="flex items-center gap-1 text-sm font-medium text-primary">Calendar <x-app-icon name="arrow-right" class="h-4 w-4" /></a></div>
                <ul class="mt-4 divide-y divide-line">
                    @forelse ($events as $e)
                        <li class="flex gap-4 py-4 first:pt-0 last:pb-0"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><x-app-icon :name="$e->type === 'meeting' ? 'users' : 'calendar'" class="h-5 w-5" /></span>
                            <div><p class="font-semibold">{{ $e->title }}</p><p class="text-sm text-ink-soft">{{ $e->when }}@if ($e->location) · {{ $e->location }}@endif @if ($e->description). {{ $e->description }}@endif</p></div></li>
                    @empty
                        <li class="py-6 text-center text-sm text-ink-soft">No upcoming events.</li>
                    @endforelse
                </ul>
            </section>

            @if ($notice)
                <section class="rounded-2xl bg-primary-soft p-6"><h2 class="flex items-center gap-2 font-semibold"><x-app-icon name="calendar" class="h-5 w-5 text-primary" /> {{ $notice->title }}</h2>
                    <p class="mt-2 text-sm text-body">{{ \Illuminate\Support\Str::limit($notice->body, 160) }}</p>
                    <a href="{{ route('parent.announcements') }}" class="mt-3 inline-block text-sm font-medium text-primary hover:underline">View all announcements →</a></section>
            @endif
        </div>
    </div>

    <footer class="mt-8 flex flex-wrap justify-between gap-2 text-sm text-ink-soft"><span>Northwood Academy · Parent workspace</span><span>Last updated today at {{ now()->format('g:i A') }}</span></footer>
</x-layouts.parent>

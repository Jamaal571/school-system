@php
    $r = fn ($name) => Route::has($name) ? route($name) : '#';
    $yr = \App\Models\AcademicYear::current();
    $termLabel = $yr ? ($yr->currentTerm()?->name ?? 'Term').', '.$yr->starts_on->format('Y').'-'.$yr->ends_on->format('y') : 'Set up the term';
    $tiles = [
        'warning' => 'bg-warning-soft text-warning',
        'danger'  => 'bg-danger-soft text-danger',
        'success' => 'bg-success-soft text-success',
    ];
    $actions = [
        ['Announce to School',     'megaphone',                'admin.communications'],
        ['Approve Leave / Budgets', 'clipboard-document-check', 'admin.approvals'],
        ['Add New User',           'user-plus',                'admin.users.create'],
        ['Generate Report',        'document-chart-bar',       'admin.reports'],
    ];
@endphp

<x-layouts.admin title="Overview" crumb="Overview">

    {{-- Heading --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">School at a glance</h1>
            <p class="mt-2 text-[15px] text-ink-soft">{{ $greeting }}, {{ $head['short'] }}. Here’s how Northwood is doing today.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ $r('admin.settings') }}" class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium">
                <x-app-icon name="calendar" class="h-5 w-5 text-ink-soft" /> {{ $termLabel }}
            </a>
            <a href="{{ $r('admin.reports') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">
                <x-app-icon name="arrow-down-tray" class="h-5 w-5" /> Generate report
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $s)
            <x-stat-card :label="$s['label']" :value="$s['value']" :icon="$s['icon']" :note="$s['note']" :tone="$s['tone']" :arrow="$s['arrow']" />
        @endforeach
    </div>

    {{-- Quick actions --}}
    <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-3">
        <p class="text-[15px] font-medium text-ink-soft">Quick actions</p>
        <div class="grid flex-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($actions as [$text, $icon, $route])
                <a href="{{ $r($route) }}" class="flex items-center justify-center gap-3 rounded-xl border border-line bg-white py-4 text-sm font-medium hover:border-primary">
                    <x-app-icon :name="$icon" class="h-5 w-5 text-primary" /> {{ $text }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Charts --}}
    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <section class="rounded-2xl border border-line bg-white p-6 xl:col-span-2" x-data="{ range: 'week' }">
            <h2 class="text-xl font-semibold">Attendance trends</h2>
            <p class="mt-1 text-sm text-ink-soft">School-wide attendance · {{ $attendance['range'] }}</p>
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-xs text-ink-soft">
                <div class="flex items-center gap-5">
                    <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-primary"></span> Attendance rate</span>
                    <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-[#A9BEF0]"></span> School target · 95%</span>
                </div>
                <div class="flex items-center gap-1 text-sm">
                    @foreach (['week' => 'Week', 'month' => 'Month'] as $key => $label)
                        <a href="{{ route('admin.dashboard', ['range' => $key]) }}"
                           @class(['rounded-lg px-3 py-1.5', 'bg-primary-soft font-medium text-primary' => $range === $key])>{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div class="mt-4">
                @if (count($attendance['points']))
                <x-line-chart label="Attendance rate this week" :points="$attendance['points']" :forecast="$attendance['forecast']"
                    :labels="$attendance['labels']" :y-min="90" :y-max="100" :ticks="[90, 95, 100]" suffix="%"
                    :smooth="true" :target="95" :tooltip="$attendance['tooltip']" />
                @else
                    <p class="py-16 text-center text-sm text-ink-soft">No attendance has been recorded yet. It appears here after the first day.</p>
                @endif
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-xl font-semibold">Enrollment growth</h2>
            <p class="mt-1 text-sm text-ink-soft">Active students by academic year</p>
            <div class="mt-5 flex items-center gap-3">
                <p class="text-3xl font-medium">{{ number_format($enrollment['current']) }}</p>
                @if ($enrollment['yoy'])
                    <span class="rounded-md bg-success-soft px-2 py-1 text-sm font-semibold text-success">{{ $enrollment['yoy'] }}</span>
                @endif
            </div>
            <div class="mt-3">
                <x-line-chart label="Active students by year" :points="$enrollment['points']" :labels="$enrollment['labels']"
                    :y-min="$enrollment['yMin']" :y-max="$enrollment['yMax']" :ticks="$enrollment['ticks']" :width="460" />
            </div>
        </section>
    </div>

    {{-- Approvals + activity --}}
    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        <section class="rounded-2xl border border-line bg-white p-6 xl:col-span-2">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Approvals awaiting you</h2>
                    <p class="mt-1 text-sm text-ink-soft">{{ $approvalCounts['pending'] }} {{ \Illuminate\Support\Str::plural('request', $approvalCounts['pending']) }} need your review · {{ $approvalCounts['urgent'] }} marked urgent</p>
                </div>
                <a href="{{ $r('admin.approvals') }}" class="flex items-center gap-1 text-sm font-medium text-primary">View all <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-[15px]">
                    <thead>
                        <tr class="bg-wash text-xs uppercase tracking-wide text-ink-soft">
                            <th class="px-3 py-3 font-medium">Request</th>
                            <th class="px-3 py-3 font-medium">Submitted by</th>
                            <th class="px-3 py-3 font-medium">Amount / Dates</th>
                            <th class="px-3 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($approvals as $a)
                            <tr>
                                <td class="px-3 py-4 font-semibold">{{ $a['title'] }}</td>
                                <td class="px-3 py-4">{{ $a['by'] }}</td>
                                <td class="px-3 py-4">{{ $a['value'] }}</td>
                                <td class="px-3 py-4"><x-badge :status="$a['status']">{{ ucfirst($a['status']) }}</x-badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-10 text-center text-ink-soft">Nothing to approve. New requests appear here.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="mt-4 border-l-[3px] border-primary pl-3 text-sm text-ink-soft">Budget approvals are required before purchase orders can be issued.</p>
        </section>

        <section class="rounded-2xl border border-line bg-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">School activity</h2>
                <a href="{{ $r('admin.approvals') }}" class="flex items-center gap-1 text-sm font-medium text-primary">View all <x-app-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            <ul class="mt-5 divide-y divide-line">
                @foreach ($activity as $item)
                    <li class="flex gap-4 py-5 first:pt-0 last:pb-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tiles[$item['tone']] }}">
                            <x-app-icon :name="$item['icon']" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-semibold">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm text-ink-soft">{{ $item['text'] }}</p>
                            <p class="mt-2 text-xs text-ink-muted">{{ $item['meta'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <footer class="mt-8 flex flex-wrap justify-between gap-2 text-sm text-ink-soft">
        <span>Northwood Academy · Academic year 2026–27</span>
        <span>Last updated today at {{ now()->format('g:i A') }}</span>
    </footer>
</x-layouts.admin>

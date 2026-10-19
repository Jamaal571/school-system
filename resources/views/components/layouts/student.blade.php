@props(['title' => 'Overview', 'crumb' => 'Overview'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Northwood Academy</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas font-sans text-ink antialiased">
@php
    $user = auth()->user();
    $me = \App\Models\Student::where('user_id', $user->id)->first();
    $classId = $me ? \App\Models\Enrollment::where('student_id', $me->id)->latest('academic_year_id')->value('school_class_id') : null;
    $due = $me ? \App\Models\Assignment::where('school_class_id', $classId)->whereDate('due_on', '>=', today())
        ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $me->id)->whereNotNull('submitted_on'))->count() : 0;
    $unread = \App\Models\Message::where('to_user_id', $user->id)->whereNull('read_at')->count();
    $year = \App\Models\AcademicYear::current();
    $term = $year?->currentTerm();
    $week = $term ? max(1, (int) floor($term->starts_on->diffInDays(today()) / 7) + 1) : null;
    $initials = collect(explode(' ', $user->name))->reject(fn ($p) => str_ends_with($p, '.'))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');

    $menu = [
        ['Overview',          'student.dashboard',   'squares-2x2',             null,            'student.dashboard'],
        ['My courses',        'student.courses',     'book-open',               null,            'student.courses*'],
        ['Assignments',       'student.assignments', 'clipboard-document-check', $due ?: null,    'student.assignments*'],
        ['Timetable',         'student.timetable',   'calendar',                null,            'student.timetable'],
        ['Grades & progress', 'student.grades',      'presentation-chart-line', null,            'student.grades'],
        ['Attendance',        'student.attendance',  'calendar-days',           null,            'student.attendance'],
        ['Communications',    'student.messages',    'chat-bubble-left-right',  $unread ?: null, ['student.messages*', 'student.announcements']],
    ];
@endphp

<div x-data="{ open: false }" class="lg:flex">
    <div x-show="open" @click="open = false" class="fixed inset-0 z-30 bg-ink/40 lg:hidden" style="display:none"></div>

    <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 flex w-60 shrink-0 flex-col border-r border-line bg-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
        <div class="flex items-center gap-3 px-6 pb-5 pt-7">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-navy text-white"><x-app-icon name="academic-cap" class="h-6 w-6" /></div>
            <div class="leading-tight"><p class="text-xl font-bold">Northwood</p><p class="text-xs uppercase tracking-wide text-ink-soft">Academy</p></div>
        </div>

        <div class="mx-4 flex items-center justify-between rounded-xl bg-wash px-4 py-3 text-sm font-semibold">Student workspace</div>

        <p class="mt-7 px-6 text-xs uppercase tracking-wide text-ink-soft">Workspace</p>
        <nav class="mt-2 flex-1 space-y-1 overflow-y-auto px-3" aria-label="Main">
            @foreach ($menu as [$label, $route, $icon, $badge, $pattern])
                @php $active = request()->routeIs(...(array) $pattern); @endphp
                <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
                   @class(['flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] transition',
                           'bg-primary-soft font-medium text-primary' => $active,
                           'text-body hover:bg-canvas hover:text-ink' => ! $active])>
                    <x-app-icon :name="$icon" class="h-[22px] w-[22px]" />
                    <span class="flex-1">{{ $label }}</span>
                    @if ($badge) <span class="text-sm font-semibold text-primary">{{ $badge }}</span> @endif
                </a>
            @endforeach
        </nav>

        <div class="mx-4 mb-3 mt-4 rounded-xl border border-line p-4">
            <p class="text-xs uppercase tracking-wide text-ink-soft">Academic year</p>
            <p class="mt-1 font-semibold">{{ $year ? str_replace('–', ' – ', $year->name) : 'Not set' }}</p>
            <p class="mt-1 flex items-center gap-2 text-sm text-ink-soft"><span class="h-2 w-2 rounded-full bg-success"></span> {{ $term ? $term->name.' · Week '.$week : 'No term set' }}</p>
        </div>

        <div class="space-y-1 px-3 pb-3">
            <a href="{{ route('student.help') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] text-body hover:bg-canvas"><x-app-icon name="question-mark-circle" class="h-[22px] w-[22px]" /> Help &amp; support</a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] text-body hover:bg-canvas"><x-app-icon name="cog-6-tooth" class="h-[22px] w-[22px]" /> Settings</a>
        </div>

        <div class="flex items-center gap-3 border-t border-line px-6 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-soft text-sm font-semibold text-primary">{{ strtoupper($initials) }}</div>
            <div class="min-w-0 flex-1 leading-tight"><p class="truncate text-sm font-semibold">{{ $user->name }}</p><p class="text-xs text-ink-soft">Student</p></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-lg p-1.5 text-body hover:bg-canvas" aria-label="Sign out">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-20 flex h-[60px] items-center gap-4 border-b border-line bg-white px-4 sm:px-8">
            <button @click="open = true" class="rounded-lg p-2 text-ink-soft lg:hidden" aria-label="Open menu"><x-app-icon name="bars-3" class="h-6 w-6" /></button>
            <nav class="flex items-center gap-2 text-sm text-ink-soft" aria-label="Breadcrumb">
                <span>Student</span><x-app-icon name="chevron-right" class="h-4 w-4" /><span class="font-medium text-ink">{{ $crumb }}</span>
            </nav>
            <span class="ml-auto hidden text-sm text-ink-soft sm:block">{{ now()->format('D, j M Y') }}</span>
        </header>

        <main class="px-4 py-8 sm:px-8">
            @if (session('status'))
                <div class="mb-6 flex items-center gap-3 rounded-xl bg-success-soft px-4 py-3 text-sm font-medium text-success" role="status">
                    <x-app-icon name="check-circle" class="h-5 w-5" /> {{ session('status') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>

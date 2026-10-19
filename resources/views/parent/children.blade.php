<x-layouts.parent title="My children" crumb="My children">
    <x-page-header title="My children" subtitle="Choose a child to see their school day." />
    <div class="mt-6 grid gap-5 md:grid-cols-2">
        @foreach ($children as $c)
            @php $st = $stats[$c->id]; $class = $c->enrollments->sortByDesc('academic_year_id')->first()?->schoolClass?->name; @endphp
            <article class="rounded-2xl border border-line bg-white p-6">
                <div class="flex items-center gap-4"><span class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-soft font-semibold text-primary">{{ strtoupper(mb_substr($c->first_name, 0, 1).mb_substr($c->last_name, 0, 1)) }}</span>
                    <div><h2 class="text-lg font-semibold">{{ $c->full_name }}</h2><p class="text-sm text-ink-soft">{{ $class ?? 'No class' }} · {{ $c->admission_no }}</p></div></div>
                <dl class="mt-5 grid grid-cols-3 gap-3 text-center text-sm">
                    <div class="rounded-xl bg-wash py-3"><dd class="text-lg font-semibold">{{ $st['average'] === null ? '—' : $st['average'].'%' }}</dd><dt class="text-xs text-ink-soft">Average</dt></div>
                    <div class="rounded-xl bg-wash py-3"><dd class="text-lg font-semibold">{{ $st['attendance'] === null ? '—' : $st['attendance'].'%' }}</dd><dt class="text-xs text-ink-soft">Attendance</dt></div>
                    <div class="rounded-xl bg-wash py-3"><dd @class(['text-lg font-semibold', 'text-warning' => $st['owing'] > 0])>${{ number_format($st['owing']) }}</dd><dt class="text-xs text-ink-soft">Fees owing</dt></div>
                </dl>
                <div class="mt-5 flex gap-3 text-sm font-medium"><a href="{{ route('parent.dashboard', ['child' => $c->id]) }}" class="rounded-lg bg-primary px-4 py-2 text-white hover:bg-primary-hover">Open dashboard</a><a href="{{ route('parent.children.show', $c) }}" class="rounded-lg border border-line px-4 py-2 hover:bg-wash">Profile</a></div>
            </article>
        @endforeach
    </div>
</x-layouts.parent>

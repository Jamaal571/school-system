<x-layouts.teacher title="My classes" crumb="My classes">
    <x-page-header title="My classes" subtitle="The classes you teach this year." />
    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($classes as $c)
            <article class="rounded-2xl border border-line bg-white p-6">
                <h2 class="text-lg font-semibold">{{ $c->name }}</h2>
                <p class="mt-1 text-sm text-ink-soft">{{ $counts[$c->id] ?? 0 }} students · Grade {{ $c->grade_level }}</p>
                <div class="mt-5 flex gap-3 text-sm font-medium">
                    <a href="{{ route('teacher.classes.show', $c) }}" class="rounded-lg border border-line px-4 py-2 hover:bg-wash">Class list</a>
                    <a href="{{ route('teacher.attendance', ['class' => $c->id]) }}" class="rounded-lg bg-primary px-4 py-2 text-white hover:bg-primary-hover">Take attendance</a>
                </div>
            </article>
        @empty
            <p class="rounded-2xl border border-line bg-white py-12 text-center text-ink-soft sm:col-span-2 xl:col-span-3">You have no classes yet. The admin adds them to your timetable.</p>
        @endforelse
    </div>
</x-layouts.teacher>

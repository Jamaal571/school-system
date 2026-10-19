<x-layouts.student title="My courses" crumb="My courses">
    <x-page-header title="My courses" subtitle="The subjects you study this year." />
    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($courses as $c)
            <a href="{{ route('student.courses.show', $c['subject']) }}" class="rounded-2xl border border-line bg-white p-6 hover:border-primary">
                <h2 class="text-lg font-semibold">{{ $c['subject']->name }}</h2>
                <p class="mt-1 text-sm text-ink-soft">{{ $c['teacher'] ?? 'Teacher to be confirmed' }}</p>
                <p class="mt-5 text-sm">Average: <span class="font-semibold">{{ isset($averages[$c['subject']->name]) ? $averages[$c['subject']->name].'%' : '—' }}</span></p>
            </a>
        @empty
            <p class="rounded-2xl border border-line bg-white py-12 text-center text-ink-soft sm:col-span-2 xl:col-span-3">Your courses appear here once your class has a timetable.</p>
        @endforelse
    </div>
</x-layouts.student>

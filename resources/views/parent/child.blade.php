@php $class = $student->enrollments->sortByDesc('academic_year_id')->first()?->schoolClass; @endphp
<x-layouts.parent :title="$student->full_name" crumb="My children">
    <x-page-header :title="$student->full_name" :subtitle="$student->admission_no.($class ? ' · '.$class->name : '')">
        <a href="{{ route('parent.dashboard', ['child' => $student->id]) }}" class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Open dashboard</a>
    </x-page-header>
    <section class="mt-6 max-w-2xl rounded-2xl border border-line bg-white p-6">
        <dl class="space-y-3 text-[15px]">
            <div class="flex justify-between"><dt class="text-ink-soft">Class</dt><dd>{{ $class?->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Homeroom teacher</dt><dd>{{ $class?->homeroomTeacher?->full_name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Gender</dt><dd>{{ ucfirst($student->gender ?? '—') }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Date of birth</dt><dd>{{ $student->date_of_birth?->format('j M Y') ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Admitted</dt><dd>{{ $student->admitted_on?->format('j M Y') ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-soft">Status</dt><dd><x-badge :status="$student->status">{{ ucfirst($student->status) }}</x-badge></dd></div>
        </dl>
        <p class="mt-6 text-sm text-ink-soft">Something wrong here? Tell the school office so they can correct it.</p>
    </section>
</x-layouts.parent>

@php
    $class = $student->enrollments->sortByDesc('academic_year_id')->first()?->schoolClass?->name;
    $guardian = $student->guardians->first();
@endphp
<x-layouts.admin :title="$student->full_name" crumb="Students">
    <x-page-header :title="$student->full_name" :subtitle="$student->admission_no.($class ? ' · '.$class : '')">
        <a href="{{ route('admin.students.index') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Back</a>
        <a href="{{ route('admin.students.edit', $student) }}" class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Edit</a>
        <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
              onsubmit="return confirm('Delete {{ $student->full_name }} and all their records? This cannot be undone.')">
            @csrf @method('DELETE')
            <button class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium text-danger hover:bg-danger-soft">Delete</button>
        </form>
    </x-page-header>

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Profile</h2>
            <dl class="mt-4 space-y-3 text-[15px]">
                <div class="flex justify-between"><dt class="text-ink-soft">Status</dt><dd><x-badge :status="$student->status">{{ ucfirst($student->status) }}</x-badge></dd></div>
                <div class="flex justify-between"><dt class="text-ink-soft">Gender</dt><dd>{{ ucfirst($student->gender ?? '—') }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-soft">Date of birth</dt><dd>{{ $student->date_of_birth?->format('j M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-soft">Admitted</dt><dd>{{ $student->admitted_on?->format('j M Y') ?? '—' }}</dd></div>
            </dl>
            <h3 class="mt-6 font-semibold">Parent or guardian</h3>
            @if ($guardian)
                <p class="mt-2">{{ $guardian->full_name }} <span class="text-ink-soft">· {{ $guardian->pivot->relationship ?? 'Guardian' }}</span></p>
                <p class="text-sm text-ink-soft">{{ $guardian->phone ?? 'No phone' }}</p>
            @else
                <p class="mt-2 text-sm text-ink-soft">None added yet.</p>
            @endif
        </section>

        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Attendance</h2>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                @foreach (['present' => 'text-success', 'late' => 'text-warning', 'absent' => 'text-danger'] as $k => $color)
                    <div class="rounded-xl bg-wash py-3"><p class="text-2xl font-semibold {{ $color }}">{{ $summary[$k] ?? 0 }}</p><p class="text-xs text-ink-soft">{{ ucfirst($k) }}</p></div>
                @endforeach
            </div>
            <ul class="mt-4 divide-y divide-line text-sm">
                @forelse ($recent as $a)
                    <li class="flex items-center justify-between py-2.5"><span>{{ $a->date->format('D j M') }}</span><x-badge :status="$a->status">{{ ucfirst($a->status) }}</x-badge></li>
                @empty
                    <li class="py-6 text-center text-ink-soft">No attendance recorded yet.</li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="text-lg font-semibold">Fees</h2>
            <ul class="mt-4 divide-y divide-line text-[15px]">
                @forelse ($student->invoices as $i)
                    <li class="flex items-center justify-between py-3">
                        <div><p class="font-medium">{{ $i->number }}</p><p class="text-sm text-ink-soft">{{ $i->description }} · due {{ $i->due_on->format('j M') }}</p></div>
                        <div class="text-right"><p class="font-medium">${{ number_format((float) $i->amount, 2) }}</p><x-badge :status="$i->status">{{ ucfirst($i->status) }}</x-badge></div>
                    </li>
                @empty
                    <li class="py-6 text-center text-ink-soft">No invoices yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.admin>

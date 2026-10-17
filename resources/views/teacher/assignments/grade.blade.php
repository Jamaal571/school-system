<x-layouts.teacher :title="$assignment->title" crumb="Assignments">
    <x-page-header :title="$assignment->title" :subtitle="$assignment->schoolClass->name.' · due '.$assignment->due_on->format('j M Y').' · out of '.$assignment->max_score">
        <a href="{{ route('teacher.assignments') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Back</a>
    </x-page-header>

    <form method="POST" action="{{ route('teacher.assignments.grades', $assignment) }}" class="mt-6">
        @csrf
        <div class="overflow-hidden rounded-2xl border border-line bg-white">
            <ul class="divide-y divide-line">
                @foreach ($rows as $e)
                    @php $sub = $subs[$e->student_id] ?? null; @endphp
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div>
                            <p class="font-medium">{{ $e->student->full_name }}</p>
                            <p class="text-sm text-ink-soft">{{ $sub?->submitted_on ? 'Submitted '.$sub->submitted_on->format('j M') : 'Not submitted' }}</p>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-ink-soft">
                            <input type="number" name="scores[{{ $e->student_id }}]" step="0.5" min="0" max="{{ $assignment->max_score }}"
                                   value="{{ old('scores.'.$e->student_id, $sub?->score !== null ? (float) $sub->score : '') }}"
                                   class="w-24 rounded-lg border-line px-3 py-1.5 text-sm" aria-label="Score for {{ $e->student->full_name }}">
                            / {{ $assignment->max_score }}
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
        @error('scores.*') <p class="mt-3 text-sm text-danger">{{ $message }}</p> @enderror
        <button class="mt-5 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Save scores</button>
    </form>
</x-layouts.teacher>

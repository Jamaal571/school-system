<x-layouts.finance title="Payroll" crumb="Payroll">
    <x-page-header title="Payroll" subtitle="Monthly salaries for active staff: ${{ number_format($monthly) }} per month." />

    @if ($errors->has('month'))
        <p class="mt-4 rounded-xl bg-danger-soft px-4 py-3 text-sm text-danger">{{ $errors->first('month') }}</p>
    @endif

    <div class="mt-6 grid gap-5 xl:grid-cols-3">
        <div class="space-y-5">
            <form method="POST" action="{{ route('finance.payroll.process') }}" class="space-y-4 rounded-2xl border border-line bg-white p-6">
                @csrf
                <h2 class="text-lg font-semibold">Process payroll</h2>
                @if ($options->isNotEmpty())
                    <label class="block"><span class="text-sm font-medium">Month</span>
                        <select name="month" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">@foreach ($options as $m)<option value="{{ $m->format('Y-m') }}">{{ $m->format('F Y') }}</option>@endforeach</select></label>
                    <p class="text-sm text-ink-soft">Posts a salaries expense of ${{ number_format($monthly) }} for the active staff.</p>
                    <button class="w-full rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover" onclick="return confirm('Post payroll for the selected month?')">Process payroll</button>
                @else
                    <p class="text-sm text-ink-soft">Payroll is up to date for the last six months.</p>
                @endif
            </form>

            <section class="rounded-2xl border border-line bg-white p-6">
                <h2 class="text-lg font-semibold">Payroll history</h2>
                <ul class="mt-3 divide-y divide-line text-[15px]">
                    @forelse ($runs as $r)
                        <li class="flex items-center justify-between py-3"><div><p class="font-medium">{{ $r->month->format('F Y') }}</p><p class="text-sm text-ink-soft">{{ $r->staff_count }} staff</p></div><p class="font-medium">${{ number_format((float) $r->total) }}</p></li>
                    @empty
                        <li class="py-6 text-center text-sm text-ink-soft">No payroll processed yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <form method="POST" action="{{ route('finance.payroll.salaries') }}" class="rounded-2xl border border-line bg-white p-6 xl:col-span-2">
            @csrf @method('PUT')
            <h2 class="text-lg font-semibold">Monthly salaries</h2>
            <ul class="mt-3 divide-y divide-line">
                @foreach ($staff as $m)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-2.5">
                        <div><p class="font-medium">{{ $m->full_name }}</p><p class="text-sm text-ink-soft">{{ $m->type === 'teacher' ? 'Teacher' : 'Support staff' }}@if ($m->status !== 'active') · inactive (not paid)@endif</p></div>
                        <label class="flex items-center gap-2 text-sm text-ink-soft">$<input type="number" name="salary[{{ $m->id }}]" step="0.01" min="0" value="{{ (float) $m->monthly_salary }}" class="w-28 rounded-lg border-line px-3 py-1.5 text-sm" aria-label="Monthly salary for {{ $m->full_name }}"></label>
                    </li>
                @endforeach
            </ul>
            <button class="mt-5 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Save salaries</button>
        </form>
    </div>
</x-layouts.finance>

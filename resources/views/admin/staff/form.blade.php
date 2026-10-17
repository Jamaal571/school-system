@php $editing = $member->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit staff member' : 'Add staff member'" crumb="Staff & teachers">
    <x-page-header :title="$editing ? 'Edit '.$member->full_name : 'Add staff member'">
        <a href="{{ route('admin.staff.index') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Cancel</a>
    </x-page-header>

    <form method="POST" action="{{ $editing ? route('admin.staff.update', $member) : route('admin.staff.store') }}"
          class="mt-6 max-w-3xl rounded-2xl border border-line bg-white p-6">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form-field name="first_name" label="First name" :value="$member->first_name" required />
            <x-form-field name="last_name" label="Last name" :value="$member->last_name" required />
            <label class="block">
                <span class="text-sm font-medium">Role</span>
                <select name="type" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                    <option value="teacher" @selected(old('type', $member->type) === 'teacher')>Teacher</option>
                    <option value="support" @selected(old('type', $member->type) === 'support')>Support staff</option>
                </select>
            </label>
            <label class="block">
                <span class="text-sm font-medium">Status</span>
                <select name="status" class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                    <option value="active" @selected(old('status', $member->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $member->status) === 'inactive')>Inactive</option>
                </select>
            </label>
            <x-form-field name="email" type="email" label="Email" :value="$member->email" />
            <x-form-field name="phone" label="Phone" :value="$member->phone" />
            <x-form-field name="hired_on" type="date" label="Hired on" :value="$member->hired_on?->toDateString()" />
        </div>
        <button class="mt-6 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">{{ $editing ? 'Save changes' : 'Add staff member' }}</button>
    </form>
</x-layouts.admin>

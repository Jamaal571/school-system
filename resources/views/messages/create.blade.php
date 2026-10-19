<x-dynamic-component :component="'layouts.'.$role" title="New message" crumb="Messages">
    <x-page-header title="New message">
        <a href="{{ route($role.'.messages') }}" class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-medium hover:bg-wash">Cancel</a>
    </x-page-header>
    @if ($recipients->isEmpty())
        <p class="mt-6 max-w-2xl rounded-2xl border border-line bg-white p-6 text-ink-soft">There is nobody to message yet. {{ $role !== 'teacher' ? 'Teachers appear here once your child’s classes have a teacher linked to a login.' : 'Parents appear here once their login is linked to a student in your classes.' }}</p>
    @else
        <form method="POST" action="{{ route($role.'.messages.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-2xl border border-line bg-white p-6">
            @csrf
            <label class="block"><span class="text-sm font-medium">To</span>
                <select name="to_user_id" required class="mt-1.5 w-full rounded-xl border-line bg-white py-2.5 text-sm">
                    <option value="">Choose {{ $role !== 'teacher' ? 'a teacher' : 'a parent' }}</option>
                    @foreach ($recipients as $r)<option value="{{ $r->id }}" @selected(old('to_user_id', $to) == $r->id)>{{ $r->name }}</option>@endforeach
                </select>@error('to_user_id') <span class="mt-1 block text-sm text-danger">{{ $message }}</span> @enderror</label>
            <label class="block"><span class="text-sm font-medium">Message</span>
                <textarea name="body" rows="6" required class="mt-1.5 w-full rounded-xl border-line px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">{{ old('body') }}</textarea>@error('body') <span class="mt-1 block text-sm text-danger">{{ $message }}</span> @enderror</label>
            <button class="rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Send message</button>
        </form>
    @endif
</x-dynamic-component>

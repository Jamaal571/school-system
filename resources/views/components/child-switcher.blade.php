@props(['children', 'child'])
@if ($children->count() > 1)
    <div class="mb-6 flex flex-wrap items-center gap-2" role="tablist" aria-label="Choose a child">
        @foreach ($children as $c)
            <a href="{{ request()->fullUrlWithQuery(['child' => $c->id]) }}" role="tab" aria-selected="{{ $c->id === $child->id ? 'true' : 'false' }}"
               @class(['rounded-full px-4 py-2 text-sm font-medium', 'bg-primary text-white' => $c->id === $child->id, 'border border-line bg-white text-body hover:bg-wash' => $c->id !== $child->id])>{{ $c->first_name }}</a>
        @endforeach
    </div>
@endif

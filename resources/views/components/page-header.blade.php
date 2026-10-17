@props(['title', 'subtitle' => null])
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-3xl font-semibold tracking-tight">{{ $title }}</h1>
        @if ($subtitle) <p class="mt-2 text-[15px] text-ink-soft">{{ $subtitle }}</p> @endif
    </div>
    <div class="flex flex-wrap items-center gap-3">{{ $slot }}</div>
</div>

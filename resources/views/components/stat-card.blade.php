@props(['label', 'value', 'icon', 'note', 'tone' => 'muted', 'arrow' => false])
@php $tones = ['success' => 'text-success', 'primary' => 'text-primary', 'muted' => 'text-ink-soft', 'danger' => 'text-danger', 'warning' => 'text-warning']; @endphp

<div {{ $attributes->class('rounded-2xl border border-line bg-white p-6') }}>
    <div class="flex items-center justify-between">
        <p class="text-[15px] text-ink-soft">{{ $label }}</p>
        <x-app-icon :name="$icon" class="h-6 w-6 text-primary" />
    </div>
    <p class="mt-4 text-[34px] font-medium leading-none tracking-tight">{{ $value }}</p>
    <p class="mt-5 flex items-center gap-1 text-sm {{ $tones[$tone] ?? $tones['muted'] }}">
        @if ($arrow) <x-app-icon :name="$arrow === 'down' ? 'arrow-down' : 'arrow-up'" class="h-4 w-4" /> @endif
        {{ $note }}
    </p>
</div>

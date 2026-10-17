@props(['name', 'label', 'type' => 'text', 'value' => null])
<label class="block">
    <span class="text-sm font-medium">{{ $label }}</span>
    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
        {{ $attributes->class('mt-1.5 w-full rounded-xl border-line bg-white px-4 py-2.5 text-sm focus:border-primary focus:ring-primary') }}>
    @error($name) <span class="mt-1 block text-sm text-danger">{{ $message }}</span> @enderror
</label>

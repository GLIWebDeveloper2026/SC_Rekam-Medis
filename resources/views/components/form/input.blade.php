@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])

<div class="grid gap-2">
    <label for="{{ $name }}" class="text-sm font-bold text-slate-800">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $attributes->merge(['class' => 'form-input']) }}>
    @if ($help)<p class="text-sm text-slate-500">{{ $help }}</p>@endif
    @error($name)<p class="text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
</div>

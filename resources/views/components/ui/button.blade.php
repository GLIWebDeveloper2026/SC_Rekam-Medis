@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-slate-300 bg-white text-slate-800 hover:bg-slate-50',
        'danger' => 'bg-danger text-white hover:bg-red-700',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
        default => 'bg-clinic-600 text-white hover:bg-clinic-700',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex min-h-11 items-center justify-center gap-2 rounded-[var(--radius-control)] px-4 py-2.5 font-bold transition active:scale-[0.98] {$classes}"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex min-h-11 items-center justify-center gap-2 rounded-[var(--radius-control)] px-4 py-2.5 font-bold transition active:scale-[0.98] {$classes}"]) }}>{{ $slot }}</button>
@endif

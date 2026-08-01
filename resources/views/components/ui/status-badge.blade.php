@props(['tone' => 'neutral'])
@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-100 text-emerald-800',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold {$classes}"]) }}>{{ $slot }}</span>

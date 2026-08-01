@props(['tone' => 'info'])
@php
    $classes = match ($tone) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'danger' => 'border-red-200 bg-red-50 text-red-900',
        default => 'border-blue-200 bg-blue-50 text-blue-900',
    };
@endphp
<div role="status" {{ $attributes->merge(['class' => "rounded-[var(--radius-control)] border p-4 text-sm font-semibold {$classes}"]) }}>{{ $slot }}</div>

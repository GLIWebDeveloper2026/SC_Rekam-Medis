@props(['tone' => 'neutral'])
@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200',
        'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200',
        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold {$classes}"]) }}>{{ $slot }}</span>

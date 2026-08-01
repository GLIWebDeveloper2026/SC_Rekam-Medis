@props(['title', 'description'])
<div {{ $attributes->merge(['class' => 'grid min-h-48 place-items-center rounded-[var(--radius-panel)] border border-dashed border-slate-300 bg-slate-50 p-8 text-center']) }}>
    <div class="max-w-md"><h3 class="font-heading text-lg font-bold text-slate-900">{{ $title }}</h3><p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $description }}</p>@if ($slot->isNotEmpty())<div class="mt-5 flex justify-center">{{ $slot }}</div>@endif</div>
</div>

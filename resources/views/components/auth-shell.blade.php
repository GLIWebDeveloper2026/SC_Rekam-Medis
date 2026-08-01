@props([
    'title',
    'eyebrow',
    'heading',
    'description',
])

<x-layouts.guest :title="$title">
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-5 py-12 sm:px-10">
        <div class="absolute -left-32 top-8 size-96 rounded-full bg-clinic-200/55 blur-3xl"></div>
        <div class="absolute -right-28 bottom-0 size-80 rounded-full bg-info/10 blur-3xl"></div>

        <div class="relative w-full max-w-lg">
            <a class="mx-auto mb-8 flex w-fit items-center gap-3" href="{{ route('home') }}">
                <span class="grid size-12 place-items-center rounded-xl bg-clinic-500 text-white shadow-lg shadow-clinic-500/20">
                    <i data-lucide="heart-pulse" class="size-7"></i>
                </span>
                <span>
                    <strong class="block font-heading text-lg text-clinic-800">Sehat Bersama</strong>
                    <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Sistem Klinik</span>
                </span>
            </a>

            <section class="panel p-6 sm:p-8">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">{{ $eyebrow }}</p>
                <h1 class="mt-3 text-3xl font-bold text-slate-950">{{ $heading }}</h1>
                <p class="mt-3 leading-7 text-slate-500">{{ $description }}</p>

                {{ $slot }}
            </section>

            @isset($footer)
                <div class="mt-6 text-center text-sm text-slate-500">{{ $footer }}</div>
            @endisset
        </div>
    </main>
</x-layouts.guest>

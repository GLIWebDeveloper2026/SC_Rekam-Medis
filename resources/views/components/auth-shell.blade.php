@props(['title', 'eyebrow', 'heading', 'description'])

<x-layouts.guest :title="$title">
    <main class="grid min-h-[100dvh] lg:grid-cols-[.9fr_1.1fr]">
        <section class="relative hidden overflow-hidden bg-clinic-900 lg:block">
            <img src="{{ asset('images/clinic/care-team.webp') }}" alt="Peralatan pemeriksaan Klinik Sehat Bersama" width="1400" height="900" class="absolute inset-0 h-full w-full object-cover opacity-55">
            <div class="absolute inset-0 bg-gradient-to-t from-clinic-950 via-clinic-900/55 to-clinic-800/20"></div>
            <div class="relative flex h-full flex-col justify-between p-10 text-white xl:p-14">
                <a class="flex items-center gap-3" href="{{ route('home') }}"><span class="grid size-11 place-items-center rounded-xl bg-white text-clinic-700"><i data-lucide="heart-pulse" class="size-6"></i></span><strong class="font-heading text-lg">Sehat Bersama</strong></a>
                <div class="max-w-lg"><h2 class="font-heading text-4xl font-bold leading-tight">Akses yang aman untuk setiap peran.</h2><p class="mt-5 text-lg leading-8 text-clinic-50">Pasien mengelola kunjungan. Staf menjalankan pelayanan sesuai kewenangan.</p></div>
                <p class="text-sm text-clinic-100">Klinik Pratama Sehat Bersama</p>
            </div>
        </section>

        <section class="flex items-center justify-center px-5 py-12 sm:px-10">
            <div class="page-enter w-full max-w-lg">
                <a class="mb-8 flex items-center gap-3 lg:hidden" href="{{ route('home') }}"><span class="grid size-11 place-items-center rounded-xl bg-clinic-600 text-white"><i data-lucide="heart-pulse" class="size-6"></i></span><strong class="font-heading text-lg text-clinic-800 dark:text-clinic-100">Sehat Bersama</strong></a>
                <section class="panel p-6 sm:p-8">
                    <p class="text-sm font-bold text-clinic-600 dark:text-clinic-300">{{ $eyebrow }}</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-950 dark:text-slate-50">{{ $heading }}</h1>
                    <p class="mt-3 leading-7 text-slate-500 dark:text-slate-400">{{ $description }}</p>
                    {{ $slot }}
                </section>
                @isset($footer)<div class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ $footer }}</div>@endisset
            </div>
        </section>
    </main>
</x-layouts.guest>

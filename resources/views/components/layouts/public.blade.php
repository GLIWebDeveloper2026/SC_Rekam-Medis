<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Jadwal praktik dan layanan Klinik Pratama Sehat Bersama.">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Klinik Pratama Sehat Bersama' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-surface text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div x-data="clinicLanding">
            <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-surface/95 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/95">
                <nav class="mx-auto flex min-h-18 max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
                    <a class="flex shrink-0 items-center gap-3" href="{{ route('home') }}" @click="closeMenu">
                        <span class="grid size-10 place-items-center rounded-xl bg-clinic-600 text-white"><i data-lucide="heart-pulse" class="size-5"></i></span>
                        <span><strong class="block font-heading text-sm text-clinic-900 dark:text-clinic-100">Sehat Bersama</strong><span class="block text-xs text-slate-500 dark:text-slate-400">Klinik Pratama</span></span>
                    </a>
                    <div class="hidden items-center gap-6 text-sm font-bold text-slate-600 dark:text-slate-300 lg:flex">
                        <a class="hover:text-clinic-700 dark:hover:text-clinic-300" href="{{ route('home') }}#layanan">Layanan</a>
                        <a class="hover:text-clinic-700 dark:hover:text-clinic-300" href="{{ route('home') }}#jadwal">Jadwal</a>
                        <a class="hover:text-clinic-700 dark:hover:text-clinic-300" href="{{ route('home') }}#tentang">Tentang</a>
                    </div>
                    <div class="flex items-center gap-2">
                        @auth
                            <x-ui.button href="{{ route('dashboard') }}">Buka akun</x-ui.button>
                        @else
                            <x-ui.button class="max-sm:hidden" href="{{ route('login') }}" variant="ghost">Masuk staf</x-ui.button>
                            <x-ui.button class="max-sm:hidden" href="{{ route('register') }}">Daftar pasien</x-ui.button>
                        @endauth
                        <button class="grid size-11 place-items-center rounded-xl border border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-200 lg:hidden" type="button" @click="toggleMenu" :aria-expanded="menuOpen" aria-controls="public-mobile-menu">
                            <i data-lucide="menu" class="size-5" :class="menuOpen ? 'hidden' : 'block'"></i>
                            <i data-lucide="x" class="size-5" :class="menuOpen ? 'block' : 'hidden'" x-cloak></i>
                            <span class="sr-only">Buka menu</span>
                        </button>
                    </div>
                </nav>
                <div class="border-t border-slate-200 bg-surface px-4 py-4 dark:border-slate-800 dark:bg-slate-950 lg:hidden" id="public-mobile-menu" :class="menuOpen ? 'block' : 'hidden'" x-cloak>
                    <div class="mx-auto grid max-w-7xl gap-2 font-bold">
                        <a class="rounded-lg px-3 py-3 hover:bg-clinic-50 dark:hover:bg-slate-900" href="{{ route('home') }}#layanan" @click="closeMenu">Layanan</a>
                        <a class="rounded-lg px-3 py-3 hover:bg-clinic-50 dark:hover:bg-slate-900" href="{{ route('home') }}#jadwal" @click="closeMenu">Jadwal</a>
                        @guest
                            <x-ui.button href="{{ route('register') }}">Daftar pasien</x-ui.button>
                            <x-ui.button href="{{ route('login') }}" variant="secondary">Masuk staf</x-ui.button>
                        @endguest
                    </div>
                </div>
            </header>

            {{ $slot }}

            <footer class="border-t border-slate-200 bg-white py-12 dark:border-slate-800 dark:bg-slate-950">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 md:grid-cols-[1.2fr_.8fr_.8fr] lg:px-8">
                    <div><strong class="font-heading text-lg">Klinik Pratama Sehat Bersama</strong><p class="mt-3 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">Informasi jadwal tersedia untuk publik. Portal pasien dapat digunakan langsung setelah membuat akun.</p></div>
                    <div><h2 class="font-bold">Kontak</h2><p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">(022) 555-0177<br>pendaftaran@sehatbersama.test</p></div>
                    <div><h2 class="font-bold">Lokasi</h2><p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">Jl. Sehat Bersama No. 17<br>Kota Bandung</p></div>
                </div>
            </footer>
        </div>
    </body>
</html>

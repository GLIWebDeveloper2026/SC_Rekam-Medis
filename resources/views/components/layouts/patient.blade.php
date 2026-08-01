<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Portal Pasien' }} - Sehat Bersama</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-surface text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-[100dvh]" x-data="patientNavigation">
            <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-surface/95 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/95">
                <nav class="mx-auto flex min-h-18 max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8" aria-label="Navigasi pasien">
                    <a class="flex items-center gap-3" href="{{ route('patient-portal.index') }}"><span class="grid size-10 place-items-center rounded-xl bg-clinic-600 text-white"><i data-lucide="heart-pulse" class="size-5"></i></span><span><strong class="block font-heading text-sm">Sehat Bersama</strong><span class="block text-xs text-slate-500 dark:text-slate-400">Portal Pasien</span></span></a>
                    <div class="hidden items-center gap-6 text-sm font-bold text-slate-600 dark:text-slate-300 md:flex">
                        <a class="hover:text-clinic-700 dark:hover:text-clinic-300" href="{{ route('patient-portal.index') }}">Dashboard</a>
                        <a class="hover:text-clinic-700 dark:hover:text-clinic-300" href="{{ route('patient-portal.index') }}#appointments">Janji temu</a>
                        <a class="hover:text-clinic-700 dark:hover:text-clinic-300" href="{{ route('patient-portal.index') }}#history">Riwayat</a>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="hidden text-right text-sm sm:block"><strong class="block">{{ auth()->user()->name }}</strong><span class="text-xs text-slate-500 dark:text-slate-400">Pasien terverifikasi</span></span>
                        <form class="hidden sm:block" method="POST" action="{{ route('logout') }}">@csrf<x-ui.button type="submit" variant="ghost">Keluar</x-ui.button></form>
                        <button class="grid size-11 place-items-center rounded-xl border border-slate-300 dark:border-slate-700 md:hidden" type="button" @click="toggle" :aria-expanded="open" aria-controls="patient-mobile-menu"><i data-lucide="menu" class="size-5"></i><span class="sr-only">Buka menu</span></button>
                    </div>
                </nav>
                <div class="border-t border-slate-200 p-4 dark:border-slate-800 md:hidden" id="patient-mobile-menu" :class="open ? 'block' : 'hidden'" x-cloak>
                    <div class="mx-auto grid max-w-7xl gap-2 font-bold"><a class="rounded-lg px-3 py-3 hover:bg-clinic-50 dark:hover:bg-slate-900" href="{{ route('patient-portal.index') }}" @click="close">Dashboard</a><a class="rounded-lg px-3 py-3 hover:bg-clinic-50 dark:hover:bg-slate-900" href="{{ route('patient-portal.index') }}#appointments" @click="close">Janji temu</a><a class="rounded-lg px-3 py-3 hover:bg-clinic-50 dark:hover:bg-slate-900" href="{{ route('patient-portal.index') }}#history" @click="close">Riwayat</a><form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button class="w-full" type="submit" variant="secondary">Keluar</x-ui.button></form></div>
                </div>
            </header>
            <main class="page-enter mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">{{ $slot }}</main>
            <x-ai.chat-panel mode="patient" />
        </div>
    </body>
</html>

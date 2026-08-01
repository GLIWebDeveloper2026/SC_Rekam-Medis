<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Sistem Klinik' }} - Sehat Bersama</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-surface text-slate-900 antialiased">
        <div class="min-h-[100dvh] lg:grid lg:grid-cols-[17rem_1fr]" x-data="navigation">
            <aside class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white px-4 py-5 transition lg:static lg:w-auto lg:translate-x-0" :class="open ? 'translate-x-0' : '-translate-x-full'">
                <div class="flex items-center justify-between gap-3 px-2">
                    <a class="flex items-center gap-3" href="{{ route('dashboard') }}"><span class="grid size-11 place-items-center rounded-xl bg-clinic-600 text-white"><i data-lucide="heart-pulse" class="size-6"></i></span><span><strong class="block font-heading text-base text-clinic-800">Sehat Bersama</strong><span class="block text-xs text-slate-400">Sistem Klinik</span></span></a>
                    <button class="grid size-10 place-items-center lg:hidden" type="button" @click="close"><i data-lucide="x" class="size-5"></i><span class="sr-only">Tutup menu</span></button>
                </div>

                <nav class="mt-8 flex-1 space-y-1 text-sm font-bold" aria-label="Navigasi aplikasi">
                    <a class="flex min-h-11 items-center gap-3 rounded-xl px-3 {{ request()->routeIs('dashboard') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-600 hover:bg-slate-50' }}" href="{{ route('dashboard') }}"><i data-lucide="layout-dashboard" class="size-5"></i> Dashboard</a>
                    @if(auth()->user()->hasPermission('patients.view'))<a class="flex min-h-11 items-center gap-3 rounded-xl px-3 {{ request()->routeIs('patients.*') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-600 hover:bg-slate-50' }}" href="{{ route('patients.index') }}"><i data-lucide="users-round" class="size-5"></i> Pasien</a>@endif
                    @if(auth()->user()->hasPermission('patients.manage'))<a class="flex min-h-11 items-center gap-3 rounded-xl px-3 {{ request()->routeIs('patient-portal-reviews.*') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-600 hover:bg-slate-50' }}" href="{{ route('patient-portal-reviews.index') }}"><i data-lucide="clipboard-check" class="size-5"></i> Persetujuan pasien</a>@endif
                    @if(auth()->user()->hasPermission('queue.view'))<a class="flex min-h-11 items-center gap-3 rounded-xl px-3 {{ request()->routeIs('queue.*','registrations.*') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-600 hover:bg-slate-50' }}" href="{{ route('queue.index') }}"><i data-lucide="list-ordered" class="size-5"></i> Antrean</a>@endif
                    @if(auth()->user()->hasPermission('clinical.view'))<a class="flex min-h-11 items-center gap-3 rounded-xl px-3 {{ request()->routeIs('clinical.*','clinical-*') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-600 hover:bg-slate-50' }}" href="{{ route('clinical.workspace') }}"><i data-lucide="stethoscope" class="size-5"></i> Klinis</a>@endif
                    @if(auth()->user()->hasPermission('pharmacy.manage'))<a class="flex min-h-11 items-center gap-3 rounded-xl px-3 {{ request()->routeIs('pharmacy.*') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-600 hover:bg-slate-50' }}" href="{{ route('pharmacy.index') }}"><i data-lucide="pill" class="size-5"></i> Farmasi</a>@endif
                    @if(auth()->user()->hasPermission('record-copies.manage'))<a class="flex min-h-11 items-center gap-3 rounded-xl px-3 {{ request()->routeIs('record-copies.*') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-600 hover:bg-slate-50' }}" href="{{ route('record-copies.index') }}"><i data-lucide="file-lock-2" class="size-5"></i> Salinan RM</a>@endif
                    @if(auth()->user()->hasPermission('reports.view'))<a class="flex min-h-11 items-center gap-3 rounded-xl px-3 {{ request()->routeIs('reports.*') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-600 hover:bg-slate-50' }}" href="{{ route('reports.index') }}"><i data-lucide="chart-no-axes-column-increasing" class="size-5"></i> Laporan</a>@endif
                </nav>

                <div class="border-t border-slate-100 pt-4">
                    <p class="px-3 text-sm font-bold">{{ auth()->user()->name }}</p>
                    <p class="px-3 text-xs text-slate-400">{{ auth()->user()->activeRoleCode() ?? 'staf' }}</p>
                    <a class="mt-3 flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-sm font-bold {{ request()->routeIs('account.*') ? 'bg-clinic-100 text-clinic-800' : 'text-slate-500 hover:bg-slate-50' }}" href="{{ route('account.security') }}"><i data-lucide="shield-check" class="size-5"></i> Keamanan akun</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-sm font-bold text-slate-500 hover:bg-red-50 hover:text-danger" type="submit"><i data-lucide="log-out" class="size-5"></i> Keluar</button></form>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="sticky top-0 z-30 flex min-h-18 items-center justify-between border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <button class="grid size-11 place-items-center rounded-xl border border-slate-200 lg:hidden" type="button" @click="toggle"><i data-lucide="menu" class="size-5"></i><span class="sr-only">Menu</span></button>
                    <div class="ml-auto text-right"><p class="text-xs font-bold text-clinic-600">Waktu klinik</p><p class="text-sm font-bold text-slate-700">{{ now(config('clinic.timezone'))->format('d-m-Y H:i') }} WIB</p></div>
                </header>
                <main class="page-enter px-4 py-8 sm:px-6 lg:px-8">{{ $slot }}</main>
            </div>

            <button class="fixed inset-0 z-40 bg-slate-900/20 lg:hidden" type="button" :class="open ? 'block' : 'hidden'" x-cloak @click="close" aria-label="Tutup menu"></button>
            <x-ai.chat-panel mode="staff" />
        </div>
    </body>
</html>

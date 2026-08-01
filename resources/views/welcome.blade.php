<x-layouts.public title="Klinik Pratama Sehat Bersama">
    <main class="page-enter">
        <section class="relative overflow-hidden" id="beranda">
            <div class="absolute inset-y-0 right-0 hidden w-[43%] bg-clinic-50 dark:bg-clinic-950/30 md:block"></div>
            <div class="mx-auto grid min-h-[calc(100dvh-4.5rem)] max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 md:grid-cols-[1.05fr_.95fr] lg:px-8">
                <div class="relative z-10 max-w-2xl">
                    <p class="text-sm font-bold text-clinic-700 dark:text-clinic-300">Perawatan keluarga yang terhubung</p>
                    <h1 class="mt-5 font-heading text-4xl font-bold leading-[1.08] tracking-tight text-slate-950 dark:text-slate-50 md:text-6xl">Layanan klinik yang jelas sejak pendaftaran.</h1>
                    <p class="mt-6 max-w-[58ch] text-lg leading-relaxed text-slate-600 dark:text-slate-300">Jadwal, pendaftaran, antrean, pemeriksaan, dan farmasi terhubung dalam satu alur layanan yang aman.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <x-ui.button href="{{ route('register') }}">Daftar sebagai pasien</x-ui.button>
                        <x-ui.button href="{{ route('login') }}" variant="secondary">Masuk staf</x-ui.button>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="absolute -bottom-5 -left-5 hidden h-36 w-32 rounded-2xl bg-clinic-700 md:block"></div>
                    <img src="{{ asset('images/clinic/hero-care.webp') }}" alt="Pendampingan pasien dengan sentuhan yang menenangkan" width="1600" height="1200" class="relative aspect-[4/3] w-full rounded-2xl object-cover shadow-[var(--shadow-panel)]" fetchpriority="high">
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" id="layanan">
            <div class="max-w-2xl"><h2 class="font-heading text-3xl font-bold text-slate-950 dark:text-slate-50 md:text-4xl">Satu alur untuk setiap tahap kunjungan</h2><p class="mt-4 leading-7 text-slate-600 dark:text-slate-300">Pasien mendapat alur yang mudah dipahami. Staf bekerja dengan akses sesuai kewenangan.</p></div>
            <div class="mt-10 grid gap-6 md:grid-cols-[1.2fr_.8fr]">
                <x-ui.panel class="md:row-span-2 md:p-8"><span class="grid size-12 place-items-center rounded-xl bg-clinic-100 text-clinic-700 dark:bg-clinic-950 dark:text-clinic-200"><i data-lucide="stethoscope" class="size-6"></i></span><h3 class="mt-8 font-heading text-2xl font-bold">Pemeriksaan umum dan gigi</h3><p class="mt-3 max-w-xl leading-7 text-slate-600 dark:text-slate-300">Pendaftaran, jadwal, antrean, pemeriksaan, tindak lanjut, dan rujukan tercatat dalam alur yang konsisten.</p></x-ui.panel>
                <x-ui.panel class="bg-clinic-50 dark:bg-clinic-950/40"><h3 class="font-heading text-xl font-bold">Farmasi terhubung</h3><p class="mt-3 text-slate-600 dark:text-slate-300">Resep dan penyerahan obat tetap mengikuti otorisasi tenaga medis.</p></x-ui.panel>
                <x-ui.panel><h3 class="font-heading text-xl font-bold">Privasi berdasarkan peran</h3><p class="mt-3 text-slate-600 dark:text-slate-300">Setiap pengguna hanya melihat informasi yang diperlukan untuk tugasnya.</p></x-ui.panel>
            </div>
        </section>

        <section class="bg-clinic-50/70 py-20 dark:bg-clinic-950/20" id="jadwal">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-sm font-bold text-clinic-700 dark:text-clinic-300">Informasi publik</p>
                <h2 class="mt-3 font-heading text-3xl font-bold text-slate-950 dark:text-slate-50 md:text-4xl">Jadwal praktik</h2>
                <div class="mt-10 grid gap-4 md:grid-cols-2">
                    @php($dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'])
                    @forelse ($schedules as $schedule)
                        <x-ui.panel class="flex items-center justify-between gap-5">
                            <div><p class="font-heading text-lg font-bold text-slate-900 dark:text-slate-100">{{ $schedule['provider'] }}</p><p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ str($schedule['service'])->headline() }}</p></div>
                            <p class="max-w-56 text-right font-bold text-clinic-700 dark:text-clinic-300">{{ collect($schedule['days'])->map(fn ($day) => $dayNames[$day] ?? 'Hari '.$day)->implode(', ') }}<br>{{ $schedule['start_time'] }}-{{ $schedule['end_time'] }} WIB</p>
                        </x-ui.panel>
                    @empty
                        <x-ui.empty-state class="md:col-span-2" title="Jadwal sedang diperbarui" description="Hubungi klinik untuk memastikan waktu layanan hari ini." />
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-20 sm:px-6 md:grid-cols-[.9fr_1.1fr] lg:px-8" id="tentang">
            <img src="{{ asset('images/clinic/care-team.webp') }}" alt="Stetoskop sebagai bagian dari layanan pemeriksaan klinik" width="1400" height="900" class="aspect-[14/9] w-full rounded-2xl object-cover shadow-[var(--shadow-panel)]" loading="lazy">
            <div><h2 class="font-heading text-3xl font-bold text-slate-950 dark:text-slate-50 md:text-4xl">Keputusan medis tetap berada pada tenaga kesehatan</h2><p class="mt-5 leading-7 text-slate-600 dark:text-slate-300">Portal dan asisten digital membantu jadwal serta alur kunjungan. Diagnosis, resep, dan tindakan klinis tetap ditangani petugas berwenang.</p></div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
            <div class="grid gap-6 rounded-2xl bg-clinic-900 p-7 text-white md:grid-cols-2 md:p-10">
                <div><h2 class="font-heading text-2xl font-bold">Jam operasional</h2><p class="mt-2 text-clinic-100">Senin-Sabtu, 07:00-21:00 WIB.</p></div>
                <div><h2 class="font-heading text-2xl font-bold">Akses portal pasien</h2><p class="mt-2 text-clinic-100">Daftar dengan email, lalu gunakan portal dan chatbot setelah pendaftaran selesai.</p></div>
            </div>
        </section>
    </main>
</x-layouts.public>

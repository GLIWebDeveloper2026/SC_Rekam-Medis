<x-layouts.guest title="Klinik Pratama Sehat Bersama">
    <div x-data="clinicLanding">
        <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur-xl">
            <nav class="mx-auto flex min-h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
                <a class="flex items-center gap-3" href="#beranda" @click="closeMenu">
                    <span class="grid size-11 place-items-center rounded-xl bg-clinic-500 text-white shadow-lg shadow-clinic-500/20"><i data-lucide="heart-pulse" class="size-6"></i></span>
                    <span><strong class="block font-heading text-base text-clinic-800">Sehat Bersama</strong><span class="block text-[10px] font-bold uppercase tracking-[0.22em] text-slate-400">Klinik Pratama</span></span>
                </a>

                <div class="hidden items-center gap-7 text-sm font-bold text-slate-600 lg:flex">
                    <a class="transition hover:text-clinic-600" href="#beranda">Beranda</a>
                    <a class="transition hover:text-clinic-600" href="#layanan">Layanan</a>
                    <a class="transition hover:text-clinic-600" href="#dokter">Dokter</a>
                    <a class="transition hover:text-clinic-600" href="#jadwal">Jadwal</a>
                    <a class="transition hover:text-clinic-600" href="#kontak">Kontak</a>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <a class="btn-primary hidden sm:inline-flex" href="{{ route('dashboard') }}">Buka dashboard</a>
                    @else
                        <a class="hidden min-h-11 items-center px-3 text-sm font-bold text-slate-600 sm:inline-flex" href="{{ route('login') }}">Masuk staf</a>
                        <button class="btn-primary hidden sm:inline-flex" type="button" @click="openModal">Buat Janji Temu</button>
                    @endauth
                    <button class="grid size-11 place-items-center rounded-xl border border-slate-200 text-slate-700 lg:hidden" type="button" @click="toggleMenu" :aria-expanded="menuOpen" aria-controls="mobile-menu">
                        <i data-lucide="menu" class="size-5" :class="menuOpen ? 'hidden' : 'block'"></i><i data-lucide="x" class="size-5" :class="menuOpen ? 'block' : 'hidden'" x-cloak></i><span class="sr-only">Buka menu</span>
                    </button>
                </div>
            </nav>
            <div class="border-t border-slate-100 bg-white px-4 py-4 lg:hidden" id="mobile-menu" :class="menuOpen ? 'block' : 'hidden'" x-cloak>
                <div class="mx-auto grid max-w-7xl gap-1 text-sm font-bold text-slate-700">
                    <a class="min-h-11 rounded-lg px-3 py-3 hover:bg-clinic-50" href="#layanan" @click="closeMenu">Layanan</a>
                    <a class="min-h-11 rounded-lg px-3 py-3 hover:bg-clinic-50" href="#dokter" @click="closeMenu">Dokter</a>
                    <a class="min-h-11 rounded-lg px-3 py-3 hover:bg-clinic-50" href="#jadwal" @click="closeMenu">Jadwal</a>
                    <button class="btn-primary mt-2" type="button" @click="openAppointment">Buat Janji Temu</button>
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden" id="beranda">
                <div class="absolute inset-y-0 right-0 hidden w-[42%] bg-clinic-50 lg:block"></div>
                <div class="absolute right-[8%] top-16 hidden size-72 rounded-full border border-clinic-200/70 lg:block"></div>
                <div class="mx-auto grid min-h-[42rem] max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-[1.08fr_0.92fr] lg:px-8">
                    <div class="relative z-10 max-w-3xl">
                        <div class="inline-flex items-center gap-2 text-sm font-extrabold uppercase tracking-[0.18em] text-clinic-700"><span class="size-2 rounded-full bg-success"></span> Terbuka untuk pasien umum & jaminan kesehatan</div>
                        <h1 class="mt-6 text-4xl font-bold leading-[1.15] text-slate-950 sm:text-5xl lg:text-6xl">Perawatan yang tenang, <span class="text-clinic-600">dekat, dan menyeluruh.</span></h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">Klinik Pratama Sehat Bersama mendampingi kesehatan keluarga dengan dokter umum, dokter gigi, keperawatan, dan farmasi dalam satu alur pelayanan yang tertata.</p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <button class="btn-primary" type="button" @click="openModal">Buat Janji Temu <i data-lucide="arrow-right" class="size-4"></i></button>
                            <a class="inline-flex min-h-11 items-center justify-center gap-2 rounded-[0.625rem] border border-clinic-300 bg-white px-5 font-bold text-clinic-700 transition hover:bg-clinic-50" href="#jadwal"><i data-lucide="calendar-days" class="size-5"></i> Lihat jadwal praktik</a>
                        </div>
                        <dl class="mt-12 grid max-w-xl grid-cols-3 gap-4 border-t border-slate-200 pt-6">
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Layanan</dt><dd class="mt-1 font-heading text-2xl font-bold text-slate-900">4+</dd></div>
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Jam buka</dt><dd class="mt-1 font-heading text-2xl font-bold text-slate-900">07–21</dd></div>
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Waktu</dt><dd class="mt-1 font-heading text-2xl font-bold text-slate-900">WIB</dd></div>
                        </dl>
                    </div>

                    <div class="relative z-10">
                        <div class="relative mx-auto max-w-md">
                            <div class="absolute -left-8 top-16 h-[75%] w-2 rounded-full bg-clinic-300"></div>
                            <div class="panel overflow-hidden border-clinic-100 bg-white p-7 sm:p-8">
                                <div class="flex items-center justify-between gap-4"><span class="grid size-14 place-items-center rounded-2xl bg-clinic-100 text-clinic-700"><i data-lucide="stethoscope" class="size-7"></i></span><span class="text-xs font-extrabold uppercase tracking-[0.16em] text-success">Siap melayani</span></div>
                                <h2 class="mt-8 text-2xl font-bold">Jadwal hari ini</h2>
                                <div class="mt-6 divide-y divide-slate-100">
                                    <div class="flex items-center justify-between gap-4 py-4"><div><p class="font-bold text-slate-800">Dokter umum</p><p class="text-sm text-slate-500">Konsultasi & pemeriksaan</p></div><strong class="text-clinic-700">07.00–21.00</strong></div>
                                    <div class="flex items-center justify-between gap-4 py-4"><div><p class="font-bold text-slate-800">Dokter gigi</p><p class="text-sm text-slate-500">Senin, Rabu, Sabtu</p></div><strong class="text-clinic-700">09.00–16.00</strong></div>
                                    <div class="flex items-center justify-between gap-4 py-4"><div><p class="font-bold text-slate-800">Farmasi</p><p class="text-sm text-slate-500">Resep & edukasi obat</p></div><strong class="text-clinic-700">07.00–21.00</strong></div>
                                </div>
                                <button class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-clinic-800 px-4 font-bold text-white transition hover:bg-clinic-900" type="button" @click="openModal"><i data-lucide="phone" class="size-4"></i> Hubungi pendaftaran</button>
                            </div>
                            <div class="absolute -bottom-6 -right-5 hidden w-56 border-l-4 border-info bg-white p-4 shadow-xl sm:block"><p class="text-xs font-bold uppercase tracking-wider text-info">Catatan</p><p class="mt-1 text-sm font-semibold text-slate-700">Kasus gawat diprioritaskan melalui triage klinis.</p></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white py-20 sm:py-24" id="layanan">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-2xl"><p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Layanan unggulan</p><h2 class="mt-3 text-3xl font-bold text-slate-950 sm:text-4xl">Satu klinik untuk kebutuhan kesehatan keluarga.</h2></div>
                    <div class="mt-12 grid gap-px overflow-hidden border border-slate-200 bg-slate-200 md:grid-cols-2 lg:grid-cols-4">
                        @foreach ([['stethoscope','Dokter umum','Pemeriksaan, diagnosis, terapi, dan rujukan yang terkoordinasi.'],['badge-plus','Dokter gigi','Perawatan kesehatan gigi tiga kali dalam seminggu.'],['activity','Triage & keperawatan','Tanda vital dan prioritas pelayanan dinilai dengan jelas.'],['pill','Farmasi klinik','Resep elektronik, edukasi, dan penyerahan obat yang aman.']] as [$icon,$title,$description])
                            <article class="group bg-white p-7 transition hover:bg-clinic-50"><span class="grid size-12 place-items-center rounded-xl bg-clinic-100 text-clinic-700 transition group-hover:bg-clinic-500 group-hover:text-white"><i data-lucide="{{ $icon }}" class="size-6"></i></span><h3 class="mt-6 text-xl font-bold">{{ $title }}</h3><p class="mt-3 leading-7 text-slate-500">{{ $description }}</p></article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="py-20 sm:py-24" id="dokter">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-end"><div><p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Tenaga kesehatan</p><h2 class="mt-3 text-3xl font-bold sm:text-4xl">Tim medis yang mendengarkan.</h2></div><p class="max-w-2xl text-lg leading-8 text-slate-600">Kami menggabungkan keputusan klinis yang teliti dengan komunikasi yang mudah dipahami—agar setiap pasien tahu apa yang sedang dilakukan dan mengapa.</p></div>
                    <div class="mt-12 grid gap-6 md:grid-cols-3">
                        @foreach ([['dr. Bima Pratama','Dokter umum','general'],['drg. Ayu Lestari','Dokter gigi','dental'],['Ners. Rina Safitri','Perawat','nursing']] as [$name,$role,$type])
                            <article class="panel overflow-hidden"><div class="grid h-52 place-items-center bg-clinic-100"><span class="grid size-24 place-items-center rounded-full border-8 border-white bg-clinic-500 text-white shadow-lg"><i data-lucide="user-round" class="size-11"></i></span></div><div class="p-6"><p class="text-xs font-bold uppercase tracking-wider text-clinic-600">{{ $role }}</p><h3 class="mt-2 text-xl font-bold">{{ $name }}</h3><p class="mt-3 text-sm leading-6 text-slate-500">Pendekatan ramah, profesional, dan berorientasi pada keselamatan pasien.</p></div></article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-clinic-800 py-20 text-white sm:py-24" id="jadwal">
                <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_0.85fr] lg:px-8">
                    <div><p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-200">Jadwal praktik</p><h2 class="mt-3 text-3xl font-bold sm:text-4xl">Datang pada waktu yang tepat.</h2><div class="mt-10 overflow-hidden border border-white/15"><div class="grid grid-cols-[1fr_auto] gap-4 border-b border-white/15 p-5"><strong>Dokter umum</strong><span>Senin–Sabtu · 07.00–21.00</span></div><div class="grid grid-cols-[1fr_auto] gap-4 border-b border-white/15 p-5"><strong>Dokter gigi</strong><span>Senin, Rabu, Sabtu · 09.00–16.00</span></div><div class="grid grid-cols-[1fr_auto] gap-4 p-5"><strong>Farmasi</strong><span>Senin–Sabtu · 07.00–21.00</span></div></div></div>
                    <div class="border-l border-clinic-600 pl-8"><span class="grid size-14 place-items-center rounded-2xl bg-white text-clinic-700"><i data-lucide="map-pin" class="size-7"></i></span><h3 class="mt-6 text-2xl font-bold">Lokasi klinik</h3><p class="mt-3 leading-7 text-clinic-100">Jl. Sehat Bersama No. 17, Kecamatan Harmoni, Kota Bandung.</p><p class="mt-6 flex items-center gap-3 font-bold"><i data-lucide="clock-3" class="size-5"></i> Pendaftaran terakhir 30 menit sebelum tutup</p><button class="mt-8 inline-flex min-h-11 items-center justify-center rounded-xl bg-white px-5 font-bold text-clinic-800" type="button" @click="openModal">Atur kunjungan</button></div>
                </div>
            </section>

            <section class="bg-white py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Cerita dari pasien</p><h2 class="mt-3 text-3xl font-bold sm:text-4xl">Pelayanan yang terasa manusiawi.</h2><div class="mt-12 grid gap-8 lg:grid-cols-3">
                    @foreach ([['Pendaftaran jelas dan perawat menjelaskan antrean dengan sabar. Orang tua saya merasa nyaman.','Ratna, keluarga pasien'],['Dokter gigi menjelaskan tindakan langkah demi langkah. Anak saya jadi tidak takut.','Dedi, orang tua pasien'],['Resep langsung diproses farmasi dan aturan minum dijelaskan ulang. Sangat membantu.','Mira, pasien']] as [$quote,$author])
                        <figure class="border-t-4 border-clinic-400 pt-6"><i data-lucide="quote" class="size-8 text-clinic-300"></i><blockquote class="mt-5 text-lg leading-8 text-slate-700">“{{ $quote }}”</blockquote><figcaption class="mt-5 text-sm font-bold text-slate-500">{{ $author }}</figcaption></figure>
                    @endforeach
                </div></div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-surface py-14" id="kontak"><div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 md:grid-cols-3 lg:px-8"><div><div class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-xl bg-clinic-500 text-white"><i data-lucide="heart-pulse" class="size-6"></i></span><strong class="font-heading text-lg text-clinic-800">Sehat Bersama</strong></div><p class="mt-4 max-w-sm leading-7 text-slate-500">Klinik keluarga yang bersih, tenang, terpercaya, dan mudah diakses.</p></div><div><h3 class="font-bold">Kontak</h3><p class="mt-4 text-slate-500">(022) 555-0177<br>pendaftaran@sehatbersama.test</p></div><div><h3 class="font-bold">Jam operasional</h3><p class="mt-4 text-slate-500">Senin–Sabtu<br>07.00–21.00 WIB</p></div></div></footer>

        <div class="fixed inset-0 z-50 place-items-center bg-slate-950/45 p-4" :class="modalOpen ? 'grid' : 'hidden'" x-cloak @keydown.escape.window="closeModal" role="dialog" aria-modal="true" aria-labelledby="appointment-title">
            <div class="panel w-full max-w-lg p-6 sm:p-8" @click.outside="closeModal">
                <div class="flex items-start justify-between gap-5"><div><p class="text-sm font-bold uppercase tracking-[0.18em] text-clinic-600">Pendaftaran</p><h2 class="mt-2 text-2xl font-bold" id="appointment-title">Buat Janji Temu</h2></div><button class="grid size-11 place-items-center rounded-xl border border-slate-200" type="button" @click="closeModal"><i data-lucide="x" class="size-5"></i><span class="sr-only">Tutup</span></button></div>
                <p class="mt-5 leading-7 text-slate-600">Hubungi petugas pendaftaran untuk memilih jadwal dokter, mendapatkan kode booking, dan menyampaikan kebutuhan khusus pasien.</p>
                <div class="mt-6 grid gap-3 sm:grid-cols-2"><a class="btn-primary" href="tel:+62225550177"><i data-lucide="phone" class="size-4"></i> (022) 555-0177</a><a class="inline-flex min-h-11 items-center justify-center rounded-xl border border-clinic-300 bg-white px-4 font-bold text-clinic-700" href="mailto:pendaftaran@sehatbersama.test">Kirim email</a></div>
                <p class="mt-5 rounded-xl bg-clinic-50 p-4 text-sm font-semibold text-clinic-800">Untuk kondisi gawat, datang langsung ke klinik agar tenaga kesehatan dapat melakukan triage.</p>
            </div>
        </div>
    </div>
</x-layouts.guest>

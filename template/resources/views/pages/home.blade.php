@extends('layouts.app')

@section('title', 'Beranda Utama')

@section('content')
<!-- Hero Section (Solid Colors - No Gradients) -->
<section class="bg-surface-off pt-14 pb-20 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Left Column -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-primary-light border border-primary/20 text-primary-dark font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
                    <span>Sistem Informasi Rekam Medis Digital WIB</span>
                </div>

                <h1 class="font-heading font-extrabold text-4xl sm:text-5xl lg:text-6xl text-slate-900 leading-[1.12]">
                    Layanan Kesehatan <span class="text-primary underline decoration-primary-light decoration-4">Higienis, Cepat</span> & Terpercaya
                </h1>

                <p class="text-slate-600 text-base sm:text-lg leading-relaxed max-w-2xl font-normal">
                    Klinik Pratama Sehat Bersama melayani hingga <strong class="text-slate-900 font-semibold">~80 pasien/hari</strong> dengan pendaftaran digital, triage darurat, dan reservasi otomatis berbasis AI.
                </p>

                <!-- Hero Action Buttons (Solid Colors) -->
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <button @click="janjiModalOpen = true" class="px-7 py-4 rounded-2xl bg-primary hover:bg-primary-dark text-white font-bold text-sm shadow-xs transition-all duration-200 flex items-center gap-2.5">
                        <i class="fa-solid fa-calendar-plus text-base"></i>
                        <span>Buat Janji Temu Pasien</span>
                    </button>

                    <button @click="aiModalOpen = true" class="px-7 py-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow-xs transition-all duration-200 flex items-center gap-2.5 border border-slate-800">
                        <i class="fa-solid fa-robot text-teal-400 text-base"></i>
                        <span>AI Auto-Scheduling</span>
                    </button>
                </div>

                <!-- Stats Bar -->
                <div class="grid grid-cols-3 gap-6 pt-8 border-t border-slate-200/80 max-w-xl">
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                        <span class="block font-heading font-extrabold text-2xl sm:text-3xl text-primary">~80</span>
                        <span class="text-xs text-slate-500 font-medium">Pasien / Hari</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                        <span class="block font-heading font-extrabold text-2xl sm:text-3xl text-emerald-600">100%</span>
                        <span class="text-xs text-slate-500 font-medium">E-Resep Digital</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                        <span class="block font-heading font-extrabold text-2xl sm:text-3xl text-secondary">WIB</span>
                        <span class="text-xs text-slate-500 font-medium">Standar Waktu WIB</span>
                    </div>
                </div>
            </div>

            <!-- Hero Right Column: Live Status Box (Solid Colors) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-md border border-slate-200/90 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center font-bold shadow-xs">
                                <i class="fa-solid fa-square-poll-vertical text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-slate-900 text-base">Antrean Pelayanan Aktif</h3>
                                <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Realtime Status Klinik
                                </span>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">WIB Sync</span>
                    </div>

                    <!-- Live Call Box (Solid Color Cards) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-emerald-50/80 p-4 rounded-2xl border border-emerald-200/80">
                            <span class="text-xs text-slate-600 font-medium block">Pemeriksaan Umum</span>
                            <span class="font-heading font-black text-3xl text-primary mt-1 block">A-024</span>
                            <span class="text-[11px] text-teal-800 font-semibold block mt-1"><i class="fa-solid fa-user-doctor mr-1"></i> Ruang Periksa 1</span>
                        </div>
                        <div class="bg-blue-50/80 p-4 rounded-2xl border border-blue-200/80">
                            <span class="text-xs text-slate-600 font-medium block">Poli Gigi</span>
                            <span class="font-heading font-black text-3xl text-secondary mt-1 block">G-008</span>
                            <span class="text-[11px] text-blue-800 font-semibold block mt-1"><i class="fa-solid fa-tooth mr-1"></i> Sen, Rab, Jum</span>
                        </div>
                    </div>

                    <!-- Role Access Quick Links -->
                    <div class="space-y-2.5 pt-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Akses Langsung Peran Operator:</span>
                        
                        <a href="{{ url('/pendaftaran') }}" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 hover:bg-primary-light/60 border border-slate-200/70 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-bold">
                                    <i class="fa-solid fa-id-card"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 group-hover:text-primary">Pendaftaran Pasien & Antrean</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-primary transition-all"></i>
                        </a>

                        <a href="{{ url('/triage') }}" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 hover:bg-primary-light/60 border border-slate-200/70 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold">
                                    <i class="fa-solid fa-user-nurse"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 group-hover:text-primary">Triage Perawat & Vital Signs</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-primary transition-all"></i>
                        </a>

                        <a href="{{ url('/dokter') }}" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 hover:bg-primary-light/60 border border-slate-200/70 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center text-xs font-bold">
                                    <i class="fa-solid fa-stethoscope"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 group-hover:text-primary">Rekam Medis & E-Resep Dokter</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-primary transition-all"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Section Layanan Unggulan (Solid Flat Design) -->
<section class="py-20 bg-white border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="px-3.5 py-1 rounded-full bg-primary-light text-primary-dark font-bold text-xs uppercase tracking-wider border border-primary/20">Kualitas Pelayanan</span>
            <h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-slate-900">Layanan Unggulan Klinik Sehat Bersama</h2>
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                Dirancang bersih, efisien, dan higienis untuk memberikan kepastian medis dan kenyamanan maksimal bagi pasien.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1 -->
            <div class="p-8 rounded-3xl bg-surface-off border border-slate-200/80 shadow-xs hover:border-primary transition-all duration-200 space-y-4 group">
                <div class="w-16 h-16 rounded-2xl bg-primary text-white flex items-center justify-center text-3xl font-bold shadow-xs">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h3 class="font-heading font-bold text-xl text-slate-900 group-hover:text-primary transition-colors">Pemeriksaan Dokter Umum</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Pemeriksaan kesehatan menyeluruh, pencegahan penyakit, keluhan fisik harian, dan rujukan spesialistik.
                </p>
            </div>

            <!-- Card 2 -->
            <div class="p-8 rounded-3xl bg-surface-off border border-slate-200/80 shadow-xs hover:border-secondary transition-all duration-200 space-y-4 group">
                <div class="w-16 h-16 rounded-2xl bg-secondary text-white flex items-center justify-center text-3xl font-bold shadow-xs">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <h3 class="font-heading font-bold text-xl text-slate-900 group-hover:text-secondary transition-colors">Pemeriksaan Dokter Gigi</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Perawatan kesehatan gigi, cleaning, penambalan, dan pembersihan karang gigi dengan peralatan steril.
                </p>
                <span class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200">Praktik: Senin, Rabu, Jumat</span>
            </div>

            <!-- Card 3 -->
            <div class="p-8 rounded-3xl bg-surface-off border border-slate-200/80 shadow-xs hover:border-rose-500 transition-all duration-200 space-y-4 group">
                <div class="w-16 h-16 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-3xl font-bold shadow-xs">
                    <i class="fa-solid fa-truck-medical"></i>
                </div>
                <h3 class="font-heading font-bold text-xl text-slate-900 group-hover:text-rose-600 transition-colors">Triage & Tindakan Darurat</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Penilaian prioritas kegawatan (Queue Override Event) untuk kasus mendesak seperti pendarahan atau luka terbuka.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- AI Scheduling Promo Banner Section (Solid Slate Background) -->
<section class="py-16 bg-slate-950 text-white relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-slate-900 p-8 sm:p-12 rounded-3xl border border-slate-800">
            <div class="lg:col-span-8 space-y-4">
                <span class="px-3.5 py-1 rounded-full bg-teal-500/20 text-teal-300 text-xs font-bold border border-teal-500/30">Fitur AI Scheduling</span>
                <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-white">Reservasi Jadwal Praktis dengan AI Assistant</h2>
                <p class="text-slate-300 text-sm leading-relaxed max-w-xl">
                    Ketik perintah suara/teks seperti <em>"Saya mau periksa ke dokter umum besok jam 9 pagi"</em>, dan AI akan memproses reservasi secara otomatis.
                </p>
            </div>
            <div class="lg:col-span-4 flex lg:justify-end">
                <button @click="aiModalOpen = true" class="w-full lg:w-auto px-8 py-4 rounded-2xl bg-primary hover:bg-primary-dark text-white font-extrabold text-sm shadow-sm transition-all duration-200 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-robot text-lg"></i>
                    <span>Coba AI Scheduling Sekarang</span>
                </button>
            </div>
        </div>
    </div>
</section>
@endsection

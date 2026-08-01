@extends('layouts.app')

@section('title', 'Beranda Utama')

@section('content')
<!-- Hero Section (Clean & Putih Minimalis) -->
<section class="relative bg-gradient-to-b from-primary-light/40 via-white to-surface-off pt-12 pb-20 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Hero Text -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary-light border border-primary/20 text-primary-dark font-medium text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Sistem Informasi Rekam Medis Digital & Terintegrasi</span>
                </div>

                <h1 class="font-heading font-bold text-3xl sm:text-4xl lg:text-5xl text-dark leading-tight">
                    Pelayanan Kesehatan Klinik yang <span class="text-primary underline decoration-primary-light decoration-wavy">Higienis, Cepat</span> & Terpercaya
                </h1>

                <p class="text-dark-muted text-base sm:text-lg leading-relaxed max-w-2xl">
                    Klinik Pratama Sehat Bersama melayani sekitar 80 pasien/hari dengan dukungan pendaftaran digital, antrean pintar, triage darurat, dan reservasi otomatis berbasis AI.
                </p>

                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ url('/register') }}" class="px-6 py-3.5 rounded-xl bg-primary hover:bg-primary-dark text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-base"></i>
                        <span>Registrasi Pasien Baru</span>
                    </a>

                    <a href="{{ url('/login') }}" class="px-6 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm shadow-md transition-all duration-200 flex items-center gap-2 border border-slate-800">
                        <i class="fa-solid fa-right-to-bracket text-teal-400 text-base"></i>
                        <span>Login Staf Operator</span>
                    </a>

                    <button @click="aiModalOpen = true" class="px-5 py-3.5 rounded-xl bg-white hover:bg-slate-50 text-dark font-semibold text-sm shadow-xs border border-slate-200 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-robot text-primary"></i>
                        <span>AI Scheduling</span>
                    </button>
                </div>

                <!-- Stats Bar -->
                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-200/80 max-w-xl">
                    <div>
                        <span class="block font-heading font-bold text-2xl text-primary">~80</span>
                        <span class="text-xs text-dark-muted font-medium">Pasien / Hari</span>
                    </div>
                    <div>
                        <span class="block font-heading font-bold text-2xl text-primary">100%</span>
                        <span class="text-xs text-dark-muted font-medium">Resep E-Digital</span>
                    </div>
                    <div>
                        <span class="block font-heading font-bold text-2xl text-primary">WIB</span>
                        <span class="text-xs text-dark-muted font-medium">Standar Waktu WIB</span>
                    </div>
                </div>
            </div>

            <!-- Right Hero Card: Antrean Pelayanan Aktif (HANYA DILIHAT SAAT SUDAH LOGIN) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-100 relative space-y-6">
                    
                    <!-- Kondisi 1: TAMPIL SAAT LOGGED IN (SUDAH LOGIN) -->
                    <template x-if="userRole">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                        <i class="fa-solid fa-notes-medical text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-heading font-bold text-slate-900 text-base">Antrean Pelayanan Aktif</h3>
                                        <span class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Live Realtime WIB
                                        </span>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold text-xs uppercase" x-text="userRole"></span>
                            </div>

                            <!-- Antrean Summary Grid -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-surface-off p-4 rounded-2xl border border-slate-100">
                                    <span class="text-xs text-dark-muted block">Dipanggil Dokter Umum</span>
                                    <span class="font-heading font-bold text-2xl text-primary">A-024</span>
                                    <span class="text-[11px] text-emerald-600 block mt-1"><i class="fa-solid fa-square-check"></i> Ruang Periksa 1</span>
                                </div>
                                <div class="bg-surface-off p-4 rounded-2xl border border-slate-100">
                                    <span class="text-xs text-dark-muted block">Dipanggil Dokter Gigi</span>
                                    <span class="font-heading font-bold text-2xl text-secondary">G-008</span>
                                    <span class="text-[11px] text-blue-600 block mt-1">Poli Gigi (Aktif)</span>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-primary-light/50 border border-primary/20 text-xs text-primary-dark font-medium flex items-center justify-between">
                                <span><i class="fa-solid fa-user-check mr-1"></i> Terautentikasi: <strong x-text="userName"></strong></span>
                                <a :href="'/' + userRole" class="font-bold text-primary hover:underline text-[11px]">Buka Portal →</a>
                            </div>
                        </div>
                    </template>

                    <!-- Kondisi 2: TAMPIL SAAT UNAUTHENTICATED (BELUM LOGIN) -->
                    <template x-if="!userRole">
                        <div class="space-y-5 text-center py-4">
                            <div class="w-16 h-16 rounded-3xl bg-amber-50 text-amber-600 flex items-center justify-center text-3xl font-bold mx-auto border border-amber-200">
                                <i class="fa-solid fa-lock"></i>
                            </div>

                            <div class="space-y-2">
                                <h3 class="font-heading font-bold text-slate-900 text-lg">Status Antrean Pelayanan Terkunci</h3>
                                <p class="text-xs text-slate-600 leading-relaxed max-w-xs mx-auto">
                                    Sesuai ketentuan PRD, status antrean pelayanan aktif dan informasi rekam medis hanya dapat diakses oleh **Staf Operator Terautentikasi**.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-left text-xs space-y-1.5">
                                <span class="font-bold text-slate-700 block"><i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i> Otorisasi Terlindungi:</span>
                                <ul class="text-[11px] text-slate-500 space-y-1 list-disc list-inside">
                                    <li>Petugas Pendaftaran (Antrean & Check-in)</li>
                                    <li>Perawat Triage (Vital Signs & Override)</li>
                                    <li>Dokter (Anamnesis & E-Resep)</li>
                                    <li>Farmasi & Pemilik Klinik</li>
                                </ul>
                            </div>

                            <div class="pt-2">
                                <a href="{{ url('/login') }}" class="block w-full py-3.5 rounded-2xl bg-primary hover:bg-primary-dark text-white font-bold text-xs shadow-md transition-all">
                                    <i class="fa-solid fa-right-to-bracket mr-1.5"></i> Login Staf untuk Melihat Antrean Pelayanan
                                </a>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Layanan Unggulan -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-3">
            <h2 class="font-heading font-bold text-2xl sm:text-3xl text-dark">Layanan Kesehatan Utama Klinik</h2>
            <p class="text-dark-muted text-sm sm:text-base">
                Didesain bersih, efisien, dan higienis untuk memberikan kenyamanan medis bagi seluruh anggota keluarga Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1 -->
            <div class="p-6 rounded-2xl bg-surface-off border border-slate-100 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-primary-light text-primary flex items-center justify-center text-2xl font-bold">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <h3 class="font-heading font-bold text-lg text-dark">Pemeriksaan Dokter Umum</h3>
                <p class="text-dark-muted text-sm leading-relaxed">
                    Konsultasi dan penanganan medis umum, pencegahan penyakit, keluhan harian, serta perujukan kesehatan.
                </p>
            </div>

            <!-- Card 2 -->
            <div class="p-6 rounded-2xl bg-surface-off border border-slate-100 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-secondary/10 text-secondary flex items-center justify-center text-2xl font-bold">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <h3 class="font-heading font-bold text-lg text-dark">Pemeriksaan Dokter Gigi</h3>
                <p class="text-dark-muted text-sm leading-relaxed">
                    Pemeriksaan gigi, pencabutan, pembersihan karang gigi, dan tindakan kedokteran gigi oleh tim profesional.
                </p>
                <span class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">Praktik 3x Seminggu</span>
            </div>

            <!-- Card 3 -->
            <div class="p-6 rounded-2xl bg-surface-off border border-slate-100 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl font-bold">
                    <i class="fa-solid fa-truck-medical"></i>
                </div>
                <h3 class="font-heading font-bold text-lg text-dark">Triage & Tindakan Darurat</h3>
                <p class="text-dark-muted text-sm leading-relaxed">
                    Penanganan prioritas untuk kondisi mendesak atau luka terbuka dengan penilaian cepat oleh perawat berpengalaman.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection

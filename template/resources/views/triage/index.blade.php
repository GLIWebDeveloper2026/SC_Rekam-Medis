@extends('layouts.app')

@section('title', 'Portal Triage & Perawat')

@section('content')
<div class="py-8 bg-surface-off min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 mb-1">
                    <i class="fa-solid fa-user-nurse"></i>
                    <span>PORTAL PERAWAT & TRIAGE</span>
                </div>
                <h1 class="font-heading font-bold text-2xl text-dark">Pemeriksaan Tanda Vital & Prioritas Triage</h1>
                <p class="text-xs text-dark-muted mt-1">Penilaian kondisi umum pasien, pengukuran vital sign, dan penyesuaian prioritas kegawatan (Queue Override).</p>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-semibold border border-emerald-200">
                <i class="fa-solid fa-clock mr-1"></i> Shift Pagi WIB
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Form Input Vital Signs & Triage -->
            <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
                <h3 class="font-heading font-bold text-base text-dark border-b border-slate-100 pb-3 flex items-center justify-between">
                    <span>Form Pemeriksaan Triage Pasien</span>
                    <span class="text-xs font-mono text-primary font-bold">Pasien: A-025 (Siti Aminah)</span>
                </h3>

                <form action="#" method="POST" class="space-y-4 text-xs">
                    <!-- Vital Signs Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <label class="block font-semibold text-slate-700 mb-1">Tekanan Darah</label>
                            <div class="flex items-center gap-1">
                                <input type="text" placeholder="120/80" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                                <span class="text-slate-500 font-medium">mmHg</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <label class="block font-semibold text-slate-700 mb-1">Nadi</label>
                            <div class="flex items-center gap-1">
                                <input type="number" placeholder="80" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                                <span class="text-slate-500 font-medium">x/mnt</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <label class="block font-semibold text-slate-700 mb-1">Suhu Tubuh</label>
                            <div class="flex items-center gap-1">
                                <input type="text" placeholder="36.5" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                                <span class="text-slate-500 font-medium">°C</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <label class="block font-semibold text-slate-700 mb-1">Pernapasan</label>
                            <div class="flex items-center gap-1">
                                <input type="number" placeholder="20" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                                <span class="text-slate-500 font-medium">x/mnt</span>
                            </div>
                        </div>
                    </div>

                    <!-- Keluhan & Skala Nyeri -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Keluhan Utama Saat Datang</label>
                            <textarea rows="3" placeholder="Keluhan yang dirasakan pasien saat kedatangan..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"></textarea>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tingkat Prioritas Kegawatan</label>
                            <select class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold">
                                <option value="normal">P3 - Hijau (Normal / Antrean Biasa)</option>
                                <option value="urgent">P2 - Kuning (Mendesak / Butuh Penanganan Cepat)</option>
                                <option value="emergency">P1 - Merah (Darurat / Luka Terbuka / Gawat)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Queue Override Section (FR-06) -->
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 space-y-2">
                        <div class="flex items-center gap-2 font-semibold text-amber-900">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                            <span>Penyesuaian Prioritas Antrean (Queue Override Event)</span>
                        </div>
                        <p class="text-[11px] text-amber-800">
                            Jika prioritas dinaikkan (misal pasien No. A-025 mendahului A-024), tuliskan alasan klinis sah di bawah ini untuk dicatat dalam audit trail.
                        </p>
                        <input type="text" placeholder="Alasan klinis: Pasien mengalami pendarahan luka terbuka mendadak..." class="w-full rounded-lg border border-amber-300 px-3 py-1.5 text-xs">
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs">
                            <i class="fa-solid fa-check mr-1.5"></i> Simpan Triage & Teruskan ke Dokter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Antrean Perawat / Triage Right Sidebar -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-base text-dark border-b border-slate-100 pb-3">Pasien Menunggu Triage</h3>
                    
                    <div class="space-y-3">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center">
                            <div>
                                <span class="text-xs font-mono font-bold text-primary">A-025</span>
                                <h4 class="font-semibold text-dark text-sm">Siti Aminah</h4>
                                <span class="text-[11px] text-slate-500">Check-in: 09:22:15 WIB</span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold text-xs">Belum Triage</span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center">
                            <div>
                                <span class="text-xs font-mono font-bold text-primary">A-026</span>
                                <h4 class="font-semibold text-dark text-sm">Bpk. Hendra Wijaya</h4>
                                <span class="text-[11px] text-slate-500">Check-in: 09:35:00 WIB</span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold text-xs">Belum Triage</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

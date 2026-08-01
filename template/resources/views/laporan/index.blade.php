@extends('layouts.app')

@section('title', 'Laporan Manajemen & Audit')

@section('content')
<div class="py-8 bg-surface-off min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-primary mb-1">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>PORTAL PEMILIK KLINIK & MANAJEMEN</span>
                </div>
                <h1 class="font-heading font-bold text-2xl text-dark">Laporan Manajemen & Audit Trail</h1>
                <p class="text-xs text-dark-muted mt-1">Laporan kunjungan harian (~80 pasien/hari), 10 penyakit terbanyak, dan audit keamanan rekam medis.</p>
            </div>

            <button type="button" onclick="alert('Laporan Bulanan Berhasil Diekspor sebagai PDF / Excel!')" class="px-4 py-2.5 rounded-xl bg-primary text-white font-semibold text-xs shadow-xs hover:bg-primary-dark">
                <i class="fa-solid fa-file-export mr-1.5"></i> Ekspor Laporan Bulanan (WIB)
            </button>
        </div>

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-2">
                <span class="text-xs text-dark-muted font-medium block">Total Pasien Hari Ini</span>
                <div class="flex items-baseline justify-between">
                    <span class="font-heading font-bold text-3xl text-primary">78</span>
                    <span class="text-xs font-medium text-emerald-600">Target ~80/hari</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-2">
                <span class="text-xs text-dark-muted font-medium block">Kunjungan Dokter Umum</span>
                <div class="flex items-baseline justify-between">
                    <span class="font-heading font-bold text-3xl text-dark">62</span>
                    <span class="text-xs text-slate-500">79.5%</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-2">
                <span class="text-xs text-dark-muted font-medium block">Kunjungan Dokter Gigi</span>
                <div class="flex items-baseline justify-between">
                    <span class="font-heading font-bold text-3xl text-secondary">16</span>
                    <span class="text-xs text-blue-600">Praktik Hari Ini</span>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-2">
                <span class="text-xs text-dark-muted font-medium block">Resep E-Digital Terpenuhi</span>
                <div class="flex items-baseline justify-between">
                    <span class="font-heading font-bold text-3xl text-emerald-600">100%</span>
                    <span class="text-xs text-emerald-600">Terbaca Lengkap</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- 10 Penyakit Terbanyak Grid -->
            <div class="lg:col-span-6 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="font-heading font-bold text-base text-dark border-b border-slate-100 pb-3">10 Penyakit Terbanyak (Bulan Ini WIB)</h3>
                
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50">
                        <span class="font-semibold text-dark">1. J06.9 - Infeksi Saluran Pernapasan Atas (ISPA)</span>
                        <span class="font-mono font-bold text-primary">142 Kasus</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50">
                        <span class="font-semibold text-dark">2. I10 - Hipertensi Primer</span>
                        <span class="font-mono font-bold text-primary">98 Kasus</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50">
                        <span class="font-semibold text-dark">3. K02.9 - Karies Gigi (Gigi Berlubang)</span>
                        <span class="font-mono font-bold text-secondary">64 Kasus</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50">
                        <span class="font-semibold text-dark">4. E11 - Diabetes Mellitus Tipe 2</span>
                        <span class="font-mono font-bold text-slate-700">45 Kasus</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50">
                        <span class="font-semibold text-dark">5. K29.7 - Gastritis / Maag</span>
                        <span class="font-mono font-bold text-slate-700">38 Kasus</span>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Log Grid -->
            <div class="lg:col-span-6 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="font-heading font-bold text-base text-dark border-b border-slate-100 pb-3 flex items-center justify-between">
                    <span>Audit Trail Keamanan Rekam Medis</span>
                    <span class="text-xs text-emerald-600 font-mono">Realtime WIB</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                        <div class="flex justify-between font-semibold text-dark">
                            <span>Finalisasi Rekam Medis #MRN-2026-08012</span>
                            <span class="font-mono text-slate-500">09:35:12 WIB</span>
                        </div>
                        <p class="text-slate-600">Pelaku: dr. Rina Astuti (Dokter) | Hash Integritas: `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                        <div class="flex justify-between font-semibold text-dark">
                            <span>Queue Override Event (Pasien A-025)</span>
                            <span class="font-mono text-slate-500">09:25:00 WIB</span>
                        </div>
                        <p class="text-slate-600">Pelaku: Ns. Dewa (Perawat) | Alasan: Pendarahan luka terbuka</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

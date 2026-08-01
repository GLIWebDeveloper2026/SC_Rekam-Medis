@extends('layouts.app')

@section('title', 'Portal Farmasi & Apoteker')

@section('content')
<div class="py-8 bg-surface-off min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-primary mb-1">
                    <i class="fa-solid fa-pills"></i>
                    <span>PORTAL FARMASI & APOTEKER</span>
                </div>
                <h1 class="font-heading font-bold text-2xl text-dark">Validasi Resep & Manajemem Stok Obat</h1>
                <p class="text-xs text-dark-muted mt-1">Pemeriksaan resep elektronik, penyiapan obat racikan/jadi, dispensing, dan kontrol stok batch obat.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-700 font-bold text-xs border border-teal-200">
                    <i class="fa-solid fa-boxes-packing mr-1"></i> Stok Obat Terpantau
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Resep Masuk & Validasi -->
            <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="font-heading font-bold text-base text-dark border-b border-slate-100 pb-3 flex items-center justify-between">
                    <span>Resep Masuk Hari Ini</span>
                    <span class="text-xs text-slate-500 font-normal">Antrean Farmasi</span>
                </h3>

                <div class="space-y-4">
                    <!-- Resep Card Item 1 -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-mono font-bold text-primary">RX-2026-08012</span>
                                <h4 class="font-heading font-bold text-dark text-base">Ahmad Subagyo (Pasien A-024)</h4>
                                <p class="text-xs text-slate-500">Dokter Peresep: dr. Rina Astuti | Waktu Resep: 09:35:00 WIB</p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold text-xs">Dalam Penyiapan</span>
                        </div>

                        <!-- Cek Alergi Obat Validation (FR-08) -->
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                            <div>
                                <span class="font-bold">STATUS VALiDASI ALERGI: PASIEN ALERGI PENISILIN</span>
                                <p class="text-[11px] text-rose-700">Resep ini aman (Paracetamol & Vit C). Tidak memuat Penisilin/Ibuprofen.</p>
                            </div>
                        </div>

                        <!-- Prescription Items -->
                        <div class="bg-white p-3 rounded-xl border border-slate-200 text-xs space-y-2">
                            <div class="flex justify-between border-b border-slate-100 pb-1.5 font-semibold text-slate-700">
                                <span>Nama Obat & Dosis</span>
                                <span>Jumlah</span>
                            </div>
                            <div class="flex justify-between text-dark">
                                <span>1. Paracetamol 500mg (3x1 sesudah makan)</span>
                                <span class="font-mono font-bold">10 Tab</span>
                            </div>
                            <div class="flex justify-between text-dark">
                                <span>2. Vitamin C 500mg (1x1 pagi hari)</span>
                                <span class="font-mono font-bold">10 Tab</span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" onclick="alert('Resep Selesai Disiapkan dan Siap Diserahkan ke Pasien!')" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs">
                                <i class="fa-solid fa-hand-holding-medical mr-1.5"></i> Serahkan Obat ke Pasien
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stok & Batch Monitoring Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-base text-dark border-b border-slate-100 pb-3">Peringatan Stok Obat</h3>
                    
                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-dark">Paracetamol 500mg</h4>
                                <span class="text-[11px] text-slate-500">Batch: B-2026A (Exp: 12-2027)</span>
                            </div>
                            <span class="font-mono font-bold text-emerald-600">450 Tab</span>
                        </div>

                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-rose-900">Amoxicillin 500mg</h4>
                                <span class="text-[11px] text-rose-700">Batch: B-2025X (Exp: 09-2026)</span>
                            </div>
                            <span class="font-mono font-bold text-rose-600">12 Tab (Hampir Habis)</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

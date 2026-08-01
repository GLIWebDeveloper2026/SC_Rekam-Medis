@extends('layouts.app')

@section('title', 'Portal Dokter & Rekam Medis')

@section('content')
<div class="py-8 bg-surface-off min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-primary mb-1">
                    <i class="fa-solid fa-stethoscope"></i>
                    <span>PORTAL DOKTER & REKAM MEDIS ELECTRONIC</span>
                </div>
                <h1 class="font-heading font-bold text-2xl text-dark">Pemeriksaan Dokter & Rekam Medis Immutable</h1>
                <p class="text-xs text-dark-muted mt-1">Pencatatan klinis, diagnosis, resep elektronik, dan Addendum rekam medis terlindungi integritasnya.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl bg-primary-light text-primary font-bold text-xs">
                    <i class="fa-solid fa-user-doctor mr-1"></i> dr. Rina Astuti (Dokter Umum)
                </span>
            </div>
        </div>

        <!-- Alert Allergy Warning Banner (Mencolok sebelum E-Resep sesuai FR-08) -->
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs">
            <i class="fa-solid fa-shield-virus text-rose-600 text-2xl mt-0.5 animate-pulse"></i>
            <div class="space-y-1">
                <h4 class="font-heading font-bold text-sm text-rose-800">PERINGATAN ALERGI PASIEN (IMMUTABLE SAFETY ALERT):</h4>
                <p class="text-xs text-rose-700 leading-relaxed">
                    Pasien <strong>Ahmad Subagyo (MRN-2026-08012)</strong> terkonfirmasi memiliki alergi berat terhadap: 
                    <span class="font-bold underline decoration-rose-400">Antibiotik Golongan Penisilin (Amoxicillin)</span> & <span class="font-bold underline decoration-rose-400">NSAID (Ibuprofen)</span>.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Workspace: Draft Rekam Medis Form -->
            <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-heading font-bold text-base text-dark">Pemeriksaan Pasien Aktif</h3>
                        <span class="text-xs text-slate-500">No. Antrean: <strong class="text-primary font-mono">A-024</strong> | MRN-2026-08012</span>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold text-xs">Drafting (Belum Final)</span>
                </div>

                <form action="#" method="POST" class="space-y-5 text-xs">
                    <!-- Anamnesis & Keluhan -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Anamnesis / Riwayat Keluhan Pasien</label>
                        <textarea rows="3" placeholder="Pasien mengeluhkan demam tinggi sejak 2 hari yang lalu disertai batuk kering..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs"></textarea>
                    </div>

                    <!-- Physical Exam & Vital Signs -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-slate-500 block">TD (Tanda Vital):</span>
                            <span class="font-mono font-bold text-dark text-sm">120/80 mmHg</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-slate-500 block">Nadi:</span>
                            <span class="font-mono font-bold text-dark text-sm">84 x/menit</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-slate-500 block">Suhu Tubuh:</span>
                            <span class="font-mono font-bold text-dark text-sm">38.2 °C</span>
                        </div>
                    </div>

                    <!-- Diagnosis ICD-10 & Terapi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Diagnosis Utama (ICD-10)</label>
                            <input type="text" placeholder="J06.9 - Infeksi Saluran Pernapasan Atas (ISPA)" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tindakan / Edukasi Medik</label>
                            <input type="text" placeholder="Istirahat cukup, konsumsi cairan hangat..." class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs">
                        </div>
                    </div>

                    <!-- E-Resep Obat -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex justify-between items-center">
                            <h4 class="font-heading font-bold text-dark text-xs uppercase tracking-wider">E-Resep Elektronik Dokter</h4>
                            <button type="button" class="text-xs text-primary font-semibold hover:underline">+ Tambah Obat</button>
                        </div>

                        <div class="space-y-2">
                            <div class="grid grid-cols-12 gap-2 items-center bg-white p-2.5 rounded-lg border border-slate-200">
                                <div class="col-span-5 font-semibold text-dark">Paracetamol 500mg (Tablet)</div>
                                <div class="col-span-3 text-slate-600">3x1 Sesudah Makan</div>
                                <div class="col-span-2 font-mono text-slate-800">10 Tablet</div>
                                <div class="col-span-2 text-right"><button type="button" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash"></i></button></div>
                            </div>

                            <div class="grid grid-cols-12 gap-2 items-center bg-white p-2.5 rounded-lg border border-slate-200">
                                <div class="col-span-5 font-semibold text-dark">Vitamin C 500mg</div>
                                <div class="col-span-3 text-slate-600">1x1 Pagi Hari</div>
                                <div class="col-span-2 font-mono text-slate-800">10 Tablet</div>
                                <div class="col-span-2 text-right"><button type="button" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash"></i></button></div>
                            </div>
                        </div>
                    </div>

                    <!-- Finalisasi & Signature Action -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500"><i class="fa-solid fa-lock text-emerald-600 mr-1"></i> Setelah difinalisasi, rekam medis akan tersimpan secara Immutable (Tidak dapat diedit bebas).</span>
                        
                        <button type="button" onclick="alert('Rekam Medis Berhasil Difinalisasi & Ditandatangani secara Digital! Hash Integritas Dibuat.')" class="px-6 py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-semibold text-xs shadow-md transition-all">
                            <i class="fa-solid fa-signature mr-1.5"></i> Finalisasi & Tanda Tangan Rekam Medis
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Sidebar: History & Addendum -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Addendum Correction Box (FR-09) -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-base text-dark border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>Addendum / Koreksi Klinis</span>
                        <span class="text-xs text-emerald-600 font-medium">Immutable Record</span>
                    </h3>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Rekam medis yang sudah difinalisasi tidak dapat dihapus. Untuk koreksi atau catatan tambahan, buat <strong>Addendum Baru</strong> di bawah ini.
                    </p>

                    <form action="#" method="POST" class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Alasan Koreksi / Catatan Tambahan</label>
                            <textarea rows="3" placeholder="Tuliskan catatan addendum medis..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"></textarea>
                        </div>
                        <button type="button" onclick="alert('Addendum Klinis Baru Berhasil Ditambahkan ke Kronologi Rekam Medis!')" class="w-full py-2.5 rounded-xl bg-slate-900 text-white font-semibold text-xs shadow-xs hover:bg-slate-800">
                            <i class="fa-solid fa-file-signature mr-1"></i> Simpan Addendum Klinis
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

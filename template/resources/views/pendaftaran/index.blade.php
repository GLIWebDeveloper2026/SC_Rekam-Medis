@extends('layouts.app')

@section('title', 'Portal Pendaftaran Pasien & Antrean')

@section('content')
<div class="py-8 bg-surface-off min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header Portal -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-primary mb-1">
                    <i class="fa-solid fa-id-card"></i>
                    <span>PORTAL PETUGAS PENDAFTARAN</span>
                </div>
                <h1 class="font-heading font-bold text-2xl text-dark">Pendaftaran Pasien & Reservasi Antrean</h1>
                <p class="text-xs text-dark-muted mt-1">Kelola data pasien baru/lama, reservasi via telepon/meja, dan pendaftaran tanpa NIK (bayi/darurat).</p>
            </div>

            <div class="flex items-center gap-3">
                <button @click="janjiModalOpen = true" class="px-4 py-2.5 rounded-xl bg-primary text-white font-semibold text-xs shadow-xs hover:bg-primary-dark transition-all">
                    <i class="fa-solid fa-user-plus mr-1"></i> Form Pendaftaran Baru
                </button>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Column: Search & Patient Selection -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-base text-dark border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>Cari Pasien (No. RM / NIK / Nama)</span>
                        <span class="text-xs text-dark-muted font-normal">Database Pasien</span>
                    </h3>

                    <div class="relative">
                        <input type="text" placeholder="Ketik NIK, No. RM (MRN-...), atau Nama..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-primary focus:ring-2 focus:ring-primary-light">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                    </div>

                    <!-- Selected Patient Card with ALLERGY WARNING BADGE -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-mono font-bold text-primary">MRN-2026-08012</span>
                                <h4 class="font-heading font-bold text-dark text-base">Bpk. Ahmad Subagyo</h4>
                                <p class="text-xs text-dark-muted">NIK: 3174092104850003 | Tanggal Lahir: 21-04-1985 (41 Thn)</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Aktif</span>
                        </div>

                        <!-- ALERGI WARNING BADGE (Mencolok sesuai FR-04 & Desain.md) -->
                        <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2 text-xs font-semibold shadow-xs">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base animate-bounce"></i>
                            <div>
                                <span class="block font-bold">PERINGATAN KESELAMATAN: MEMILIKI ALERGI OBAT!</span>
                                <span class="text-[11px] font-normal text-rose-700">Pasien teridentifikasi memiliki alergi (Detail disembunyikan untuk kerahasiaan medis).</span>
                            </div>
                        </div>

                        <div class="pt-2 flex gap-2">
                            <button class="w-full py-2 rounded-lg bg-primary text-white font-semibold text-xs shadow-xs hover:bg-primary-dark">
                                <i class="fa-solid fa-ticket mr-1"></i> Ambil Nomor Antrean Pasien
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Queue Status -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-heading font-bold text-base text-dark">Daftar Antrean Hari Ini (WIB)</h3>
                        <span class="text-xs font-mono bg-slate-100 px-3 py-1 rounded-full text-slate-600">Total: 42 Pasien</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                                    <th class="p-3">No. Antrean</th>
                                    <th class="p-3">Nama Pasien</th>
                                    <th class="p-3">Layanan / Poli</th>
                                    <th class="p-3">Waktu Check-in</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-bold text-primary text-sm">A-024</td>
                                    <td class="p-3 font-semibold text-dark">Ahmad Subagyo</td>
                                    <td class="p-3">Dokter Umum</td>
                                    <td class="p-3 font-mono">09:15:00 WIB</td>
                                    <td class="p-3"><span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-semibold">Diperiksa</span></td>
                                    <td class="p-3"><button class="px-2.5 py-1 rounded bg-slate-200 hover:bg-slate-300 font-medium">Panggil</button></td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-bold text-primary text-sm">A-025</td>
                                    <td class="p-3 font-semibold text-dark">Siti Aminah (Bayi)</td>
                                    <td class="p-3">Dokter Umum</td>
                                    <td class="p-3 font-mono">09:22:15 WIB</td>
                                    <td class="p-3"><span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-semibold">Menunggu</span></td>
                                    <td class="p-3"><button class="px-2.5 py-1 rounded bg-primary text-white font-medium">Panggil</button></td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono font-bold text-secondary text-sm">G-008</td>
                                    <td class="p-3 font-semibold text-dark">Budi Kurniawan</td>
                                    <td class="p-3">Dokter Gigi</td>
                                    <td class="p-3 font-mono">09:30:00 WIB</td>
                                    <td class="p-3"><span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold">Check-in</span></td>
                                    <td class="p-3"><button class="px-2.5 py-1 rounded bg-primary text-white font-medium">Panggil</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

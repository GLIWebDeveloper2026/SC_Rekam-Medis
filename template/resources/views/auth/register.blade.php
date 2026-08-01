@extends('layouts.app')

@section('title', 'Registrasi Pasien Baru')

@section('content')
<div class="py-12 bg-surface-off min-h-[85vh]">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-xs space-y-2 text-center sm:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200">
                <i class="fa-solid fa-id-card-clip text-emerald-600"></i>
                <span>Ketentuan FR-02 PRD: Pendaftaran Pasien Baru / Bayi</span>
            </div>
            <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900">Formulir Registrasi Pasien Baru</h1>
            <p class="text-xs text-slate-600">
                NIK bersifat opsional saat pendaftaran (cocok untuk pasien bayi atau kasus darurat). Nomor Rekam Medis (MRN) akan diterbitkan secara otomatis oleh sistem.
            </p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200/90 shadow-md space-y-6"
             x-data="{
                 isInfant: false,
                 registeredMrn: null,
                 submitRegistration() {
                     this.registeredMrn = 'MRN-' + new Date().getFullYear() + '-' + Math.floor(10000 + Math.random() * 90000);
                 }
             }">

            <!-- Registered Success Alert -->
            <div x-show="registeredMrn" x-transition class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>REGISTRASI PASIEN BERHASIL!</span>
                </div>
                <p class="text-xs text-emerald-800">
                    Nomor Rekam Medis Internal (MRN) Pasien: <strong class="font-mono text-emerald-950 font-black text-sm" x-text="registeredMrn"></strong>
                </p>
                <div class="pt-2 flex gap-2">
                    <a href="{{ url('/pendaftaran') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs">
                        <i class="fa-solid fa-ticket mr-1"></i> Teruskan ke Antrean Pendaftaran
                    </a>
                </div>
            </div>

            <form @submit.prevent="submitRegistration()" x-show="!registeredMrn" class="space-y-5 text-xs">
                
                <!-- Toggle Pasien Bayi / Anak -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800 block">Pendaftaran Pasien Bayi / Anak Tanpa NIK?</span>
                        <span class="text-[11px] text-slate-500">NIK dapat ditambahkan kemudian sebagai identifier baru.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="isInfant" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <!-- Input Field Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Pasien <span class="text-rose-500">*</span></label>
                        <input type="text" required placeholder="Contoh: Bpk. Ahmad Subagyo / Bayi Ny. Siti" class="w-full rounded-2xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-medium">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NIK (Nomor Induk Kependudukan) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="text" maxlength="16" placeholder="317409..." class="w-full rounded-2xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-mono">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Lahir (WIB) <span class="text-rose-500">*</span></label>
                        <input type="date" required class="w-full rounded-2xl border border-slate-300 px-3 py-2.5 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-medium">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select required class="w-full rounded-2xl border border-slate-300 px-3 py-2.5 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-medium">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenis Penjamin / Pembayaran <span class="text-rose-500">*</span></label>
                        <select required class="w-full rounded-2xl border border-slate-300 px-3 py-2.5 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-medium">
                            <option value="umum">Umum (Mandiri)</option>
                            <option value="bpjs">Jaminan Kesehatan (BPJS)</option>
                            <option value="asuransi">Asuransi Swasta</option>
                        </select>
                    </div>
                </div>

                <!-- Section Data Wali (Tampil jika Bayi / Diperlukan) -->
                <div x-show="isInfant" x-transition class="p-4 rounded-2xl bg-teal-50/70 border border-teal-200 space-y-3">
                    <span class="font-bold text-teal-900 text-xs block"><i class="fa-solid fa-users mr-1"></i> Data Orang Tua / Wali (FR-02 Bayi):</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Ibu / Ayah / Wali</label>
                            <input type="text" placeholder="Nama Ibu/Ayah..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Hubungan Keluarga</label>
                            <select class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                                <option value="ibu">Ibu Kandung</option>
                                <option value="ayah">Ayah Kandung</option>
                                <option value="wali">Wali / Keluarga</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor HP / WhatsApp <span class="text-rose-500">*</span></label>
                        <input type="tel" required placeholder="08123456789" class="w-full rounded-2xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Tinggal Pasien</label>
                        <input type="text" placeholder="Jl. Kesehatan Raya No..." class="w-full rounded-2xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-medium">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                    <a href="{{ url('/login') }}" class="text-slate-500 hover:text-slate-800 font-semibold">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Login
                    </a>

                    <button type="submit" class="px-6 py-3.5 rounded-2xl bg-primary hover:bg-primary-dark text-white font-extrabold text-xs shadow-xs transition-all flex items-center gap-2">
                        <i class="fa-solid fa-user-check"></i>
                        <span>Daftarkan Pasien & Terbitkan MRN</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

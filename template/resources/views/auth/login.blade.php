@extends('layouts.app')

@section('title', 'Login Staf Operasional')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-surface-off">
    <div class="max-w-md w-full space-y-6"
         x-data="{ 
             selectedRole: 'pendaftaran',
             email: 'pendaftaran@sehatbersama.id',
             password: 'password123',
             remember: true,
             rolesMap: {
                 'pendaftaran': { name: 'Siti Rahma', email: 'pendaftaran@sehatbersama.id', redirect: '{{ url('/pendaftaran') }}' },
                 'perawat': { name: 'Ns. Dewa', email: 'perawat@sehatbersama.id', redirect: '{{ url('/triage') }}' },
                 'dokter': { name: 'dr. Rina Astuti', email: 'dokter@sehatbersama.id', redirect: '{{ url('/dokter') }}' },
                 'apoteker': { name: 'Apt. Andi', email: 'apoteker@sehatbersama.id', redirect: '{{ url('/farmasi') }}' },
                 'pemilik': { name: 'Bpk. Hendra (Pemilik)', email: 'pemilik@sehatbersama.id', redirect: '{{ url('/laporan') }}' }
             },
             updateRole(roleKey) {
                 this.selectedRole = roleKey;
                 this.email = this.rolesMap[roleKey].email;
             },
             doLogin() {
                 const target = this.rolesMap[this.selectedRole];
                 localStorage.setItem('user_role', this.selectedRole);
                 localStorage.setItem('user_email', this.email);
                 localStorage.setItem('user_name', target.name);
                 
                 // Redirect to the authorized portal
                 window.location.href = target.redirect;
             }
         }">

        <!-- Brand Header -->
        <div class="text-center space-y-3">
            <div class="w-16 h-16 rounded-3xl bg-primary text-white flex items-center justify-center text-3xl font-bold mx-auto shadow-xs">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900">Masuk Sistem Informasi Klinik</h2>
            <p class="text-slate-600 text-xs sm:text-sm">
                Autentikasi Akun Individual Staf & Tenaga Medis (FR-01)
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200/90 shadow-md space-y-6">
            
            <!-- Role Selection Selector -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Pilih Peran Pengguna Staf:</label>
                <select x-model="selectedRole" @change="updateRole($event.target.value)" class="w-full rounded-2xl border border-slate-300 px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary-light">
                    <option value="pendaftaran">📋 Petugas Pendaftaran & Antrean</option>
                    <option value="perawat">🩺 Perawat (Triage & Vital Signs)</option>
                    <option value="dokter">👨‍⚕️ Dokter Umum / Dokter Gigi</option>
                    <option value="apoteker">💊 Apoteker (Farmasi & Stok)</option>
                    <option value="pemilik">📊 Pemilik Klinik (Laporan Agregat)</option>
                </select>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="doLogin()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Email / NIP Operator Peran</label>
                    <div class="relative">
                        <input type="email" x-model="email" required placeholder="email@sehatbersama.id" class="w-full rounded-2xl border border-slate-300 pl-10 pr-4 py-3 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-medium">
                        <i class="fa-solid fa-envelope absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kata Sandi Individual</label>
                    <div class="relative">
                        <input type="password" x-model="password" required placeholder="••••••••" class="w-full rounded-2xl border border-slate-300 pl-10 pr-4 py-3 text-xs focus:border-primary focus:ring-2 focus:ring-primary-light font-medium">
                        <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-slate-600 font-medium cursor-pointer">
                        <input type="checkbox" x-model="remember" class="rounded border-slate-300 text-primary focus:ring-primary">
                        <span>Ingat Sesi Perangkat</span>
                    </label>
                    <span class="text-[11px] text-teal-700 font-bold"><i class="fa-solid fa-key mr-1"></i> 2FA Enforced</span>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-primary hover:bg-primary-dark text-white font-extrabold text-xs shadow-xs transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket text-sm"></i>
                    <span>Masuk ke Modul Peran Terautentikasi</span>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center text-xs gap-2">
                <span class="text-slate-500">Apakah Anda Pasien Baru?</span>
                <a href="{{ url('/register') }}" class="font-bold text-primary hover:underline">
                    <i class="fa-solid fa-user-plus mr-1"></i> Registrasi Pasien Baru
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

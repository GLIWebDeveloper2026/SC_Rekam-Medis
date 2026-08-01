<!DOCTYPE html>
<html lang="id" class="h-full bg-surface-off scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Klinik Pratama Sehat Bersama') - Sistem Informasi Klinik Digital</title>

    <!-- Google Fonts (Poppins & Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="h-full flex flex-col font-body text-dark antialiased selection:bg-primary-light selection:text-primary-dark" 
      x-data="{ 
          mobileMenuOpen: false, 
          aiModalOpen: false, 
          janjiModalOpen: false,
          userRole: localStorage.getItem('user_role') || null,
          userName: localStorage.getItem('user_name') || 'Operator Staf',
          logout() {
              localStorage.removeItem('user_role');
              localStorage.removeItem('user_email');
              localStorage.removeItem('user_name');
              window.location.href = '{{ url('/') }}';
          }
      }">

    <!-- Top Status Bar -->
    <div class="bg-primary text-white text-xs py-2 px-4 font-medium flex items-center justify-between">
        <div class="max-w-7xl mx-auto flex items-center justify-between w-full">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-amber-300"></i>
                <span>Jam Operasional WIB: <strong>Senin - Sabtu (08:00 - 21:00 WIB)</strong> | Dokter Gigi: Sen, Rab, Jum (15:00 - 18:00 WIB)</span>
            </div>
            <span class="hidden md:inline font-semibold">Klinik Pratama Sehat Bersama</span>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                
                <!-- Logo Branding -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
                        <div class="w-11 h-11 rounded-xl bg-primary-light text-primary flex items-center justify-center font-bold group-hover:bg-primary group-hover:text-white transition-all">
                            <i class="fa-solid fa-heart-pulse text-2xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-heading font-bold text-xl text-dark tracking-tight leading-none">Sehat Bersama</span>
                                <span class="px-2 py-0.5 rounded bg-primary-light text-primary-dark font-bold text-[10px] uppercase">Klinik Pratama</span>
                            </div>
                            <span class="text-[11px] text-dark-muted font-medium">Sistem Informasi Terintegrasi WIB</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links - FITUR KHUSUS PERAN AKTIF (RBAC PRD Section 9) -->
                <div class="hidden lg:flex items-center space-x-2">
                    <template x-if="userRole === 'pendaftaran'">
                        <a href="{{ url('/pendaftaran') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-primary bg-primary-light font-bold">
                            <i class="fa-solid fa-id-card mr-1.5"></i> Modul Pendaftaran & Antrean
                        </a>
                    </template>

                    <template x-if="userRole === 'perawat'">
                        <a href="{{ url('/triage') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-primary bg-primary-light font-bold">
                            <i class="fa-solid fa-user-nurse mr-1.5"></i> Modul Triage & Vital Signs
                        </a>
                    </template>

                    <template x-if="userRole === 'dokter'">
                        <a href="{{ url('/dokter') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-primary bg-primary-light font-bold">
                            <i class="fa-solid fa-stethoscope mr-1.5"></i> Workspace Dokter & Rekam Medis
                        </a>
                    </template>

                    <template x-if="userRole === 'apoteker'">
                        <a href="{{ url('/farmasi') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-primary bg-primary-light font-bold">
                            <i class="fa-solid fa-pills mr-1.5"></i> Modul Farmasi & Stok Obat
                        </a>
                    </template>

                    <template x-if="userRole === 'pemilik'">
                        <a href="{{ url('/laporan') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-primary bg-primary-light font-bold">
                            <i class="fa-solid fa-chart-pie mr-1.5"></i> Laporan Agregat Pemilik
                        </a>
                    </template>
                </div>

                <!-- Action CTAs -->
                <div class="hidden md:flex items-center space-x-3">
                    <button @click="aiModalOpen = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition-all">
                        <i class="fa-solid fa-robot text-teal-400"></i>
                        <span>AI Scheduling</span>
                    </button>

                    <a href="{{ url('/register') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-primary-light text-primary hover:bg-primary hover:text-white transition-all">
                        <i class="fa-solid fa-user-plus text-sm"></i>
                        <span>Registrasi Pasien</span>
                    </a>

                    <!-- User Account / Logout -->
                    <template x-if="userRole">
                        <div class="flex items-center gap-2 bg-slate-100 p-1.5 pr-3 rounded-xl border border-slate-200">
                            <div class="w-7 h-7 rounded-lg bg-primary text-white flex items-center justify-center font-bold text-xs">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="text-left">
                                <span class="block text-xs font-bold text-dark leading-tight" x-text="userName"></span>
                                <span class="text-[10px] text-primary-dark font-bold uppercase" x-text="userRole"></span>
                            </div>
                            <button @click="logout()" title="Keluar Sesi" class="ml-2 text-rose-500 hover:text-rose-700 p-1">
                                <i class="fa-solid fa-right-from-bracket text-sm"></i>
                            </button>
                        </div>
                    </template>

                    <template x-if="!userRole">
                        <a href="{{ url('/login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-primary hover:bg-primary-dark text-white shadow-xs transition-all">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Login Staf</span>
                        </a>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center lg:hidden gap-2">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2.5 rounded-xl text-dark hover:bg-slate-100">
                        <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark text-xl' : 'fa-bars text-xl'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen" x-collapse x-cloak class="lg:hidden border-t border-slate-100 bg-white px-4 pt-3 pb-6 space-y-2">
            <template x-if="userRole === 'pendaftaran'">
                <a href="{{ url('/pendaftaran') }}" class="block px-3.5 py-2 rounded-xl text-sm font-bold text-primary bg-primary-light">Modul Pendaftaran</a>
            </template>
            <template x-if="userRole === 'perawat'">
                <a href="{{ url('/triage') }}" class="block px-3.5 py-2 rounded-xl text-sm font-bold text-primary bg-primary-light">Modul Triage Perawat</a>
            </template>
            <template x-if="userRole === 'dokter'">
                <a href="{{ url('/dokter') }}" class="block px-3.5 py-2 rounded-xl text-sm font-bold text-primary bg-primary-light">Workspace Dokter</a>
            </template>
            <template x-if="userRole === 'apoteker'">
                <a href="{{ url('/farmasi') }}" class="block px-3.5 py-2 rounded-xl text-sm font-bold text-primary bg-primary-light">Modul Farmasi</a>
            </template>
            <template x-if="userRole === 'pemilik'">
                <a href="{{ url('/laporan') }}" class="block px-3.5 py-2 rounded-xl text-sm font-bold text-primary bg-primary-light">Portal Pemilik Klinik</a>
            </template>
            <a href="{{ url('/register') }}" class="block px-3.5 py-2 rounded-xl text-sm font-bold text-dark bg-slate-100">Registrasi Pasien Baru</a>

            <div class="pt-3 border-t border-slate-100">
                <template x-if="userRole">
                    <button @click="logout()" class="w-full py-2.5 rounded-xl bg-rose-600 text-white font-bold text-xs">
                        Keluar Akun Staf
                    </button>
                </template>
                <template x-if="!userRole">
                    <a href="{{ url('/login') }}" class="block w-full py-2.5 text-center rounded-xl bg-primary text-white font-bold text-xs">
                        Login Staf Operator
                    </a>
                </template>
            </div>
        </div>
    </nav>

    <!-- Main Content Body -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer (Standard Clean Design) -->
    <footer class="bg-slate-900 text-white pt-12 pb-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-8 border-b border-slate-800">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center font-bold">
                            <i class="fa-solid fa-heart-pulse text-xl"></i>
                        </div>
                        <span class="font-heading font-bold text-lg text-white">Klinik Sehat Bersama</span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Sistem Informasi Klinik Terintegrasi khusus staf & tenaga medis terautentikasi (WIB).
                    </p>
                </div>

                <div>
                    <h4 class="font-heading font-semibold text-white mb-3 text-xs uppercase tracking-wider">Pendaftaran Pasien</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="{{ url('/register') }}" class="hover:text-primary transition-colors">Registrasi Pasien Baru (FR-02)</a></li>
                        <li><a href="{{ url('/register') }}" class="hover:text-primary transition-colors">Pendaftaran Pasien Bayi Tanpa NIK</a></li>
                        <li><a href="{{ url('/login') }}" class="hover:text-primary transition-colors">Login Akun Staf</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-heading font-semibold text-white mb-3 text-xs uppercase tracking-wider">Jam Operasional WIB</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex justify-between border-b border-slate-800 pb-1.5"><span>Senin - Sabtu:</span> <span class="text-white font-medium">08:00 - 21:00 WIB</span></li>
                        <li class="flex justify-between border-b border-slate-800 pb-1.5"><span>Dokter Gigi:</span> <span class="text-teal-400 font-medium">Sen, Rab, Jum</span></li>
                        <li class="flex justify-between"><span>Kapasitas:</span> <span class="text-white font-medium">~80 Pasien/Hari</span></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-heading font-semibold text-white mb-3 text-xs uppercase tracking-wider">Keamanan & Audit</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-primary"></i>
                            <span>Audit Trail `user_id` Per-Aksi</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-lock text-primary"></i>
                            <span>Rekam Medis Immutable Entry</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row justify-between items-center text-[11px] text-slate-500 gap-4">
                <p>&copy; 2026 Klinik Pratama Sehat Bersama. Seluruh Hak Cipta Dilindungi. Sistem Informasi Klinik Terintegrasi WIB.</p>
            </div>
        </div>
    </footer>

    <!-- Modals -->
    @include('components.ai-scheduling-modal')
    @include('components.janji-temu-modal')

    @stack('scripts')
</body>
</html>

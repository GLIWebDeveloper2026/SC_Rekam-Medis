<x-layouts.guest title="Masuk · Sehat Bersama">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative hidden overflow-hidden bg-clinic-800 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-24 top-12 size-80 rounded-full border border-white/10"></div>
            <div class="absolute -bottom-32 -left-20 size-96 rounded-full bg-clinic-500/30 blur-3xl"></div>
            <a class="relative flex items-center gap-3" href="{{ route('home') }}">
                <span class="grid size-12 place-items-center rounded-xl bg-white text-clinic-700">
                    <i data-lucide="heart-pulse" class="size-7"></i>
                </span>
                <span class="font-heading text-xl font-bold">Sehat Bersama</span>
            </a>

            <div class="relative max-w-xl">
                <p class="mb-5 text-sm font-bold uppercase tracking-[0.24em] text-clinic-200">Ruang kerja klinik terintegrasi</p>
                <h1 class="text-4xl font-bold leading-tight xl:text-5xl">Pelayanan lebih tertata, keputusan klinis lebih aman.</h1>
                <p class="mt-6 max-w-lg text-lg leading-8 text-clinic-100">Satu alur untuk identitas pasien, antrean, pemeriksaan, resep, farmasi, dan laporan dengan jejak audit yang dapat ditelusuri.</p>
            </div>

            <p class="relative text-sm text-clinic-200">Waktu operasional sistem: Asia/Jakarta (WIB)</p>
        </section>

        <section class="flex items-center justify-center px-5 py-12 sm:px-10">
            <div class="w-full max-w-md">
                <a class="mb-10 flex items-center gap-3 lg:hidden" href="{{ route('home') }}">
                    <span class="grid size-11 place-items-center rounded-xl bg-clinic-500 text-white">
                        <i data-lucide="heart-pulse" class="size-6"></i>
                    </span>
                    <span class="font-heading text-lg font-bold text-clinic-800">Sehat Bersama</span>
                </a>

                <div class="panel p-6 sm:p-8">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Akses staf</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">Masuk ke sistem klinik</h1>
                    <p class="mt-3 leading-7 text-slate-500">Gunakan akun individual Anda. Aktivitas penting dicatat untuk keamanan pasien.</p>

                    <form class="mt-8 space-y-5" method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700" for="email">Alamat email</label>
                            <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                            @error('email')
                                <p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-4">
                                <label class="text-sm font-bold text-slate-700" for="password">Kata sandi</label>
                                <a class="text-xs font-bold text-clinic-700 hover:text-clinic-800" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                            </div>
                            <input class="form-input" id="password" name="password" type="password" autocomplete="current-password" required>
                            @error('password')
                                <p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                            <input class="size-4 rounded border-slate-300 text-clinic-500 focus:ring-clinic-500" name="remember" type="checkbox" value="1">
                            Ingat sesi pada perangkat ini
                        </label>
                        <button class="btn-primary w-full" type="submit">Masuk dengan aman</button>
                    </form>
                </div>

                <p class="mt-6 text-center text-sm text-slate-500">Akun terkunci? Hubungi administrator klinik.</p>
                <p class="mt-3 text-center text-sm text-slate-500">Belum memiliki akun? <a class="font-bold text-clinic-700 hover:text-clinic-800" href="{{ route('register') }}">Daftar sekarang</a></p>
            </div>
        </section>
    </main>
</x-layouts.guest>

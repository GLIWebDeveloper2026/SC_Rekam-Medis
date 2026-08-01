<x-layouts.app title="Keamanan akun">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Pengaturan pribadi</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">Keamanan akun</h1>
            <p class="mt-2 text-slate-500">Kelola identitas, kata sandi, verifikasi email, dan autentikasi dua faktor.</p>
        </div>
        <span class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-clinic-100 px-4 font-bold text-clinic-800">
            <i data-lucide="shield-check" class="size-5"></i>
            Area terkonfirmasi
        </span>
    </div>

    @if (session('status'))
        <p class="mt-6 rounded-xl bg-success/10 px-4 py-3 text-sm font-semibold text-success" role="status">Pengaturan akun berhasil diperbarui.</p>
    @endif

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="panel p-6">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-clinic-600">Identitas</p>
            <h2 class="mt-2 text-xl font-bold text-slate-950">Profil dan identitas</h2>

            <form class="mt-6 grid gap-5" method="POST" action="{{ route('user-profile-information.update') }}">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700" for="name">Nama lengkap</label>
                    <input class="form-input" id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" required>
                    @error('name', 'updateProfileInformation')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700" for="username">Username</label>
                    <input class="form-input" id="username" name="username" type="text" value="{{ old('username', auth()->user()->username) }}" required>
                    @error('username', 'updateProfileInformation')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700" for="email">Alamat email</label>
                    <input class="form-input" id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required>
                    @error('email', 'updateProfileInformation')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
                </div>
                <button class="btn-primary w-full sm:w-fit" type="submit">Simpan profil</button>
            </form>

            @if (! auth()->user()->hasVerifiedEmail())
                <div class="mt-6 rounded-xl border border-info/20 bg-info/5 p-4">
                    <p class="font-bold text-slate-800">Email belum diverifikasi</p>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Akses aplikasi tetap tersedia. Verifikasi membantu memastikan email pemulihan dapat digunakan.</p>
                    <form class="mt-3" method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="text-sm font-bold text-info hover:underline" type="submit">Kirim ulang tautan verifikasi</button>
                    </form>
                </div>
            @endif
        </section>

        <section class="panel p-6">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-clinic-600">Kredensial</p>
            <h2 class="mt-2 text-xl font-bold text-slate-950">Perbarui kata sandi</h2>

            <form class="mt-6 grid gap-5" method="POST" action="{{ route('user-password.update') }}">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700" for="current_password">Kata sandi saat ini</label>
                    <input class="form-input" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                    @error('current_password', 'updatePassword')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700" for="password">Kata sandi baru</label>
                    <input class="form-input" id="password" name="password" type="password" autocomplete="new-password" required>
                    @error('password', 'updatePassword')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700" for="password_confirmation">Konfirmasi kata sandi baru</label>
                    <input class="form-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
                <button class="btn-primary w-full sm:w-fit" type="submit">Perbarui kata sandi</button>
            </form>
        </section>
    </div>

    <section class="panel mt-6 p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-clinic-600">Proteksi tambahan</p>
                <h2 class="mt-2 text-xl font-bold text-slate-950">Autentikasi dua faktor</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">2FA bersifat opsional dan memakai aplikasi autentikator berbasis TOTP.</p>
            </div>
            <span class="w-fit rounded-full px-3 py-1 text-xs font-bold {{ auth()->user()->hasEnabledTwoFactorAuthentication() ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-500' }}">
                {{ auth()->user()->hasEnabledTwoFactorAuthentication() ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        @if (auth()->user()->two_factor_secret === null)
            <form class="mt-6" method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <button class="btn-primary" type="submit">Aktifkan 2FA</button>
            </form>
        @elseif (auth()->user()->two_factor_confirmed_at === null)
            <div class="mt-6 grid gap-6 lg:grid-cols-[auto_1fr]">
                <div class="w-fit rounded-2xl border border-slate-200 bg-white p-4">{!! auth()->user()->twoFactorQrCodeSvg() !!}</div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Pindai kode QR</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Pindai dengan aplikasi autentikator, lalu masukkan kode enam digit untuk menyelesaikan aktivasi.</p>
                    <form class="mt-5 flex flex-col gap-3 sm:flex-row" method="POST" action="{{ route('two-factor.confirm') }}">
                        @csrf
                        <input class="form-input sm:max-w-xs" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" required>
                        <button class="btn-primary" type="submit">Konfirmasi 2FA</button>
                    </form>
                    @error('code', 'confirmTwoFactorAuthentication')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>
        @else
            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_auto]">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Recovery codes</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Simpan kode berikut di tempat aman. Setiap kode hanya dapat digunakan satu kali.</p>
                    <div class="mt-4 grid gap-2 rounded-xl bg-slate-950 p-4 font-mono text-sm text-slate-100 sm:grid-cols-2">
                        @foreach (auth()->user()->recoveryCodes() as $recoveryCode)
                            <span>{{ $recoveryCode }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                        @csrf
                        <button class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-200 px-4 font-bold text-slate-600 hover:bg-slate-50" type="submit">Buat kode baru</button>
                    </form>
                    <form method="POST" action="{{ route('two-factor.disable') }}">
                        @csrf
                        @method('DELETE')
                        <button class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-danger px-4 font-bold text-white hover:bg-red-600" type="submit">Nonaktifkan 2FA</button>
                    </form>
                </div>
            </div>
        @endif
    </section>
</x-layouts.app>

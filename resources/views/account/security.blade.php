<x-layouts.app title="Keamanan akun">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Pengaturan pribadi</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">Keamanan akun</h1>
            <p class="mt-2 text-slate-500">Kelola identitas dan kata sandi akun Anda.</p>
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

</x-layouts.app>

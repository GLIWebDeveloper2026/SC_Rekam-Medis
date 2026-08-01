<x-auth-shell
    title="Registrasi - Sehat Bersama"
    eyebrow="Portal pasien"
    heading="Daftar sebagai pasien"
    description="Buat akun pasien dan langsung gunakan portal setelah pendaftaran selesai."
>
    <form class="mt-8 grid gap-5" method="POST" action="{{ route('register.store') }}">
        @csrf

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="name">Nama lengkap</label>
            <input class="form-input" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
            @error('name')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="username">Username</label>
            <input class="form-input" id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required>
            <p class="mt-2 text-xs text-slate-400">Gunakan huruf, angka, titik, garis bawah, atau tanda hubung.</p>
            @error('username')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="email">Alamat email</label>
            <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="birth_date">Tanggal lahir</label>
            <input class="form-input" id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" required>
            @error('birth_date')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="sex">Jenis kelamin</label>
            <select class="form-input" id="sex" name="sex" required>
                <option value="">Pilih jenis kelamin</option>
                <option value="female" @selected(old('sex') === 'female')>Perempuan</option>
                <option value="male" @selected(old('sex') === 'male')>Laki-laki</option>
                <option value="unknown" @selected(old('sex') === 'unknown')>Belum diketahui</option>
            </select>
            @error('sex')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="phone">Nomor HP</label>
            <input class="form-input" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required>
            @error('phone')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="password">Kata sandi</label>
            <input class="form-input" id="password" name="password" type="password" autocomplete="new-password" required>
            @error('password')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="password_confirmation">Konfirmasi kata sandi</label>
            <input class="form-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button class="btn-primary w-full" type="submit">Daftar sebagai pasien</button>
    </form>

    <x-slot:footer>
        Sudah memiliki akun? <a class="font-bold text-clinic-700 hover:text-clinic-800" href="{{ route('login') }}">Masuk sekarang</a>
    </x-slot:footer>
</x-auth-shell>

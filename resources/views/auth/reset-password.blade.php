<x-auth-shell
    title="Kata sandi baru - Sehat Bersama"
    eyebrow="Pemulihan akun"
    heading="Buat kata sandi baru"
    description="Gunakan kata sandi baru yang kuat dan berbeda dari kata sandi sebelumnya."
>
    <form class="mt-8 grid gap-5" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input name="token" type="hidden" value="{{ $request->route('token') }}">

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="email">Alamat email</label>
            <input class="form-input" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" autocomplete="email" required autofocus>
            @error('email')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="password">Kata sandi baru</label>
            <input class="form-input" id="password" name="password" type="password" autocomplete="new-password" required>
            @error('password')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="password_confirmation">Konfirmasi kata sandi</label>
            <input class="form-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <button class="btn-primary w-full" type="submit">Simpan kata sandi baru</button>
    </form>
</x-auth-shell>

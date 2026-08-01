<x-auth-shell
    title="Konfirmasi kata sandi - Sehat Bersama"
    eyebrow="Area sensitif"
    heading="Konfirmasi kata sandi"
    description="Masukkan kembali kata sandi Anda sebelum membuka pengaturan keamanan akun."
>
    <form class="mt-8 grid gap-5" method="POST" action="{{ route('password.confirm.store') }}">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="password">Kata sandi</label>
            <input class="form-input" id="password" name="password" type="password" autocomplete="current-password" required autofocus>
            @error('password')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>
        <button class="btn-primary w-full" type="submit">Konfirmasi dan lanjutkan</button>
    </form>
</x-auth-shell>

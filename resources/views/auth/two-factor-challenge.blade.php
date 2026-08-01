<x-auth-shell
    title="Verifikasi dua faktor - Sehat Bersama"
    eyebrow="Lapisan keamanan kedua"
    heading="Konfirmasi autentikasi dua faktor"
    description="Masukkan kode dari aplikasi autentikator. Jika perangkat tidak tersedia, gunakan salah satu recovery code."
>
    <form class="mt-8 grid gap-5" method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="code">Kode autentikator</label>
            <input class="form-input" id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" autofocus>
            @error('code')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-3 text-xs font-bold uppercase tracking-wider text-slate-400">
            <span class="h-px flex-1 bg-slate-200"></span>
            atau
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="recovery_code">Recovery code</label>
            <input class="form-input" id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code">
            @error('recovery_code')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>

        <button class="btn-primary w-full" type="submit">Verifikasi dan masuk</button>
    </form>
</x-auth-shell>

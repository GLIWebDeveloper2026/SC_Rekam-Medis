<x-auth-shell
    title="Masuk - Sehat Bersama"
    eyebrow="Akses akun"
    heading="Masuk ke sistem klinik"
    description="Pasien dan staf menggunakan akun masing-masing untuk membuka layanan sesuai hak aksesnya."
>
    <form class="mt-8 grid gap-5" method="POST" action="{{ route('login.store') }}">
        @csrf
        <x-form.input name="email" type="email" label="Alamat email" :value="old('email')" autocomplete="username" required autofocus />
        <div class="grid gap-2">
            <div class="flex items-center justify-between gap-4"><label class="text-sm font-bold text-slate-800" for="password">Kata sandi</label><a class="text-xs font-bold text-clinic-700" href="{{ route('password.request') }}">Lupa kata sandi?</a></div>
            <input class="form-input" id="password" name="password" type="password" autocomplete="current-password" required>
            @error('password')<p class="text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>
        <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600"><input class="size-4 rounded border-slate-300 text-clinic-600 focus:ring-clinic-600" name="remember" type="checkbox" value="1">Ingat sesi pada perangkat ini</label>
        <x-ui.button class="w-full" type="submit">Masuk</x-ui.button>
    </form>
    <x-slot:footer>Belum memiliki akun pasien? <a class="font-bold text-clinic-700" href="{{ route('register') }}">Daftar sekarang</a></x-slot:footer>
</x-auth-shell>

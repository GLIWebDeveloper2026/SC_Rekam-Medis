<x-auth-shell
    title="Lupa kata sandi - Sehat Bersama"
    eyebrow="Pemulihan akun"
    heading="Atur ulang kata sandi"
    description="Masukkan email akun Anda. Sistem akan mengirim tautan pemulihan jika alamat tersebut terdaftar."
>
    @if (session('status'))
        <p class="mt-6 rounded-xl bg-success/10 px-4 py-3 text-sm font-semibold text-success" role="status">{{ session('status') }}</p>
    @endif

    <form class="mt-8 grid gap-5" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700" for="email">Alamat email</label>
            <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email')<p class="mt-2 text-sm font-semibold text-danger" role="alert">{{ $message }}</p>@enderror
        </div>
        <button class="btn-primary w-full" type="submit">Kirim tautan pemulihan</button>
    </form>

    <x-slot:footer>
        <a class="font-bold text-clinic-700 hover:text-clinic-800" href="{{ route('login') }}">Kembali ke halaman masuk</a>
    </x-slot:footer>
</x-auth-shell>

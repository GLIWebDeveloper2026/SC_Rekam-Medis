<x-auth-shell
    title="Verifikasi email - Sehat Bersama"
    eyebrow="Keamanan akun"
    heading="Verifikasi alamat email"
    description="Verifikasi email diperlukan sebelum pasien dapat melihat status persetujuan dan menggunakan portal."
>
    @if (session('status') === 'verification-link-sent')
        <p class="mt-6 rounded-xl bg-success/10 px-4 py-3 text-sm font-semibold text-success" role="status">Tautan verifikasi baru telah dikirim.</p>
    @endif

    <div class="mt-8 grid gap-3 sm:grid-cols-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn-primary w-full" type="submit">Kirim ulang verifikasi</button>
        </form>
        <a class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-4 font-bold text-slate-600 hover:bg-slate-50" href="{{ route('dashboard') }}">Lanjut ke dashboard</a>
    </div>
</x-auth-shell>

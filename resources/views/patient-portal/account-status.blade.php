<x-layouts.guest title="Status Akun Pasien">
    <main class="mx-auto flex min-h-[100dvh] max-w-3xl items-center px-4 py-16 sm:px-6">
        <section class="panel w-full p-7 sm:p-10">
            <a class="inline-flex items-center gap-2 font-bold text-clinic-700" href="{{ route('home') }}">Klinik Sehat Bersama</a>
            <p class="mt-8 text-sm font-bold uppercase tracking-[0.16em] text-clinic-600">Status akun pasien</p>

            @if ($account->status === \App\Models\PatientPortalAccount::StatusPending)
                <h1 class="mt-3 text-3xl font-bold text-slate-950">Menunggu persetujuan staf</h1>
                <p class="mt-4 leading-7 text-slate-600">Data pendaftaran Anda sudah diterima. Staf akan mencocokkannya dengan data pasien sebelum portal dan chatbot dapat digunakan.</p>
            @elseif ($account->status === \App\Models\PatientPortalAccount::StatusRejected)
                <h1 class="mt-3 text-3xl font-bold text-slate-950">Permintaan perlu diperbaiki</h1>
                <p class="mt-4 leading-7 text-slate-600">Staf belum dapat menghubungkan akun ini dengan data pasien. Hubungi pendaftaran untuk memperbarui data.</p>
            @else
                <h1 class="mt-3 text-3xl font-bold text-slate-950">Akses portal sedang ditangguhkan</h1>
                <p class="mt-4 leading-7 text-slate-600">Silakan hubungi staf klinik untuk memulihkan akses portal pasien.</p>
            @endif

            @if ($account->review_notes)
                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-bold text-slate-700">Catatan staf</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $account->review_notes }}</p>
                </div>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a class="btn-primary" href="mailto:pendaftaran@sehatbersama.test">Hubungi pendaftaran</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="inline-flex min-h-11 w-full items-center justify-center rounded-[0.625rem] border border-slate-300 px-5 font-bold text-slate-700" type="submit">Keluar</button>
                </form>
            </div>
        </section>
    </main>
</x-layouts.guest>

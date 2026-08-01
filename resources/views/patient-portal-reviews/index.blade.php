<x-layouts.app title="Persetujuan Akun Pasien">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-clinic-600">Verifikasi manual</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-950">Persetujuan akun pasien</h1>
        <p class="mt-2 max-w-2xl text-slate-600">Cocokkan data yang diklaim dengan data pasien. Tidak ada OTP atau persetujuan otomatis.</p>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-clinic-200 bg-clinic-50 p-4 font-semibold text-clinic-800" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 grid gap-6">
        @forelse ($accounts as $account)
            <article class="panel p-6">
                <div class="grid gap-6 xl:grid-cols-[1fr_1.15fr]">
                    <div>
                        <p class="text-sm font-bold text-clinic-700">{{ $account->user->name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $account->user->email }}</p>
                        <dl class="mt-5 grid gap-4 sm:grid-cols-3">
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Tanggal lahir</dt><dd class="mt-1 font-bold">{{ $account->claimed_birth_date->format('d-m-Y') }}</dd></div>
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Nomor HP</dt><dd class="mt-1 font-bold">{{ $account->claimed_phone }}</dd></div>
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Nomor RM</dt><dd class="mt-1 font-bold">{{ $account->claimed_medical_record_number ?: 'Tidak diisi' }}</dd></div>
                        </dl>
                        <a class="mt-5 inline-flex font-bold text-clinic-700" href="{{ route('patients.index', ['q' => $account->claimed_medical_record_number ?: $account->claimed_phone]) }}">Cari kandidat pasien</a>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <form class="rounded-xl border border-clinic-200 bg-clinic-50 p-4" method="POST" action="{{ route('patient-portal-reviews.approve', $account) }}">
                            @csrf
                            <label class="block text-sm font-bold" for="patient_id_{{ $account->id }}">ID pasien yang cocok</label>
                            <input class="form-input mt-2" id="patient_id_{{ $account->id }}" name="patient_id" type="text" required>
                            <label class="mt-3 block text-sm font-bold" for="approve_notes_{{ $account->id }}">Catatan persetujuan</label>
                            <textarea class="form-input mt-2 min-h-24" id="approve_notes_{{ $account->id }}" name="review_notes"></textarea>
                            <button class="btn-primary mt-4 w-full" type="submit">Setujui akun</button>
                        </form>
                        <form class="rounded-xl border border-red-200 bg-red-50 p-4" method="POST" action="{{ route('patient-portal-reviews.reject', $account) }}">
                            @csrf
                            <label class="block text-sm font-bold" for="reject_notes_{{ $account->id }}">Alasan penolakan</label>
                            <textarea class="form-input mt-2 min-h-32" id="reject_notes_{{ $account->id }}" name="review_notes" required></textarea>
                            <button class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-[0.625rem] bg-red-700 px-5 font-bold text-white" type="submit">Tolak permintaan</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="panel p-10 text-center text-slate-500">Tidak ada akun pasien yang menunggu persetujuan.</div>
        @endforelse
    </div>

    @if ($accounts->hasPages())<div class="mt-6">{{ $accounts->links() }}</div>@endif
</x-layouts.app>

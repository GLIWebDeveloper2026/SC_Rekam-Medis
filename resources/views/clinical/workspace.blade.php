<x-layouts.app title="Ruang Kerja Klinis">
    @if (session('status'))<div class="mb-5 rounded-xl bg-success/10 p-4 font-semibold text-success">{{ session('status') }}</div>@endif
    <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Pelayanan klinis</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Ruang kerja klinis</h1>
    <p class="mt-3 max-w-2xl leading-7 text-slate-500">Draft dapat disunting sebelum finalisasi. Setelah ditandatangani, koreksi wajib dibuat sebagai addendum.</p>

    <div class="mt-8 grid gap-5">
        @forelse($encounters as $encounter)
            <article class="panel grid gap-6 p-6 lg:grid-cols-[18rem_1fr]">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-clinic-600">{{ $encounter->service_type }} · {{ $encounter->status }}</p>
                    <h2 class="mt-2 text-xl font-bold">{{ $encounter->visit->patient->full_name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $encounter->visit->patient->medical_record_number }}</p>
                </div>
                <form class="grid gap-4" method="POST" action="{{ route('clinical-drafts.store', $encounter) }}">@csrf
                    <select class="form-input" name="entry_type"><option value="assessment">Assessment</option><option value="anamnesis">Anamnesis</option><option value="examination">Pemeriksaan</option><option value="plan">Rencana</option></select>
                    <textarea class="form-input min-h-32" name="content" required>{{ $drafts->get($encounter->id)?->content_json['text'] ?? '' }}</textarea>
                    <div class="flex justify-end"><button class="btn-primary" type="submit">Simpan draft</button></div>
                </form>
            </article>
        @empty
            <div class="panel p-10 text-center text-slate-500">Tidak ada encounter aktif untuk pengguna ini.</div>
        @endforelse
    </div>
</x-layouts.app>

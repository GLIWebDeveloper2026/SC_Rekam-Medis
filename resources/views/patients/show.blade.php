<x-layouts.app :title="$patient->full_name">
    @if (session('status'))<div class="mb-5 rounded-xl bg-success/10 p-4 font-semibold text-success" role="status">{{ session('status') }}</div>@endif
    @if ($activeAllergies->isNotEmpty())
        <section class="mb-6 rounded-2xl border border-danger/25 bg-danger/8 p-5" aria-label="Peringatan alergi">
            <p class="font-heading text-lg font-bold text-danger">Memiliki alergi obat</p>
            <p class="mt-1 text-sm font-semibold text-slate-600">Pastikan peringatan alergi diakui sebelum terapi atau penyerahan obat.</p>
            @if ($canViewClinical)
                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                    @foreach ($activeAllergies as $allergy)<li><strong>{{ $allergy->substance_name }}</strong> · {{ $allergy->reaction ?: 'Reaksi belum dicatat' }} · {{ $allergy->severity }}</li>@endforeach
                </ul>
            @endif
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
        <section class="panel p-6">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">{{ $patient->medical_record_number }}</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $patient->full_name }}</h1>
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-bold uppercase text-slate-400">Tanggal lahir</dt><dd class="mt-1 font-semibold">{{ $patient->birth_date->format('d-m-Y') }}</dd></div>
                <div><dt class="text-xs font-bold uppercase text-slate-400">Jenis kelamin</dt><dd class="mt-1 font-semibold">{{ $patient->sex }}</dd></div>
                <div><dt class="text-xs font-bold uppercase text-slate-400">Telepon</dt><dd class="mt-1 font-semibold">{{ $patient->phone ?: '—' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase text-slate-400">Status</dt><dd class="mt-1 font-semibold">{{ $patient->status }}</dd></div>
            </dl>
        </section>

        <aside class="panel p-6">
            <h2 class="text-lg font-bold">Identifier</h2>
            <p class="mt-2 text-sm text-slate-500">Nilai sensitif dilindungi; tipe dan status tetap dapat ditelusuri.</p>
            <ul class="mt-4 space-y-3">
                @foreach ($patient->identifiers as $identifier)<li class="rounded-xl bg-slate-50 p-3 text-sm"><strong class="block uppercase text-slate-700">{{ $identifier->identifier_type }}</strong><span class="text-slate-500">{{ $identifier->verified_status }}</span></li>@endforeach
            </ul>
        </aside>
    </div>
</x-layouts.app>

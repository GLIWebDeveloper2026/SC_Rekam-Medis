<x-layouts.app title="Farmasi">
    @if(session('status'))<div class="mb-5 rounded-xl bg-success/10 p-4 font-semibold text-success">{{ session('status') }}</div>@endif
    <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Farmasi & keselamatan obat</p>
    <h1 class="mt-2 text-3xl font-bold">Antrean resep</h1>
    <p class="mt-2 text-slate-500">Resep final, koreksi, substitusi, dispensing, dan batch ditelusuri sebagai event.</p>
    <div class="mt-8 space-y-5">
        @forelse($prescriptions as $prescription)
            <article class="panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3"><span class="rounded-full bg-clinic-100 px-3 py-1 text-xs font-bold uppercase text-clinic-700">{{ $prescription->status }}</span><time class="text-sm text-slate-500">{{ $prescription->finalized_at->format('d-m-Y H:i:s') }} WIB</time></div>
                <ul class="mt-4 space-y-3">@foreach($prescription->items as $item)<li class="rounded-xl bg-slate-50 p-4"><strong>{{ $item->medicine_name_snapshot }} {{ $item->strength_snapshot }}</strong><p class="mt-1 text-sm text-slate-600">{{ $item->dosage }} · {{ $item->frequency }} · {{ $item->route }} · jumlah {{ $item->quantity }}</p></li>@endforeach</ul>
            </article>
        @empty<div class="panel p-10 text-center text-slate-500">Belum ada resep pada antrean farmasi.</div>@endforelse
    </div>
</x-layouts.app>

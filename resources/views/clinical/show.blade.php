<x-layouts.app title="Timeline Klinis">
    @if (session('status'))<div class="mb-5 rounded-xl bg-success/10 p-4 font-semibold text-success">{{ session('status') }}</div>@endif
    <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Rekam medis immutable</p>
    <h1 class="mt-2 text-3xl font-bold">Timeline catatan klinis</h1>
    <div class="mt-8 space-y-5">
        @foreach($timeline as $entry)
            <article class="panel p-6 {{ $entry->entry_status === 'addendum' ? 'border-info/30' : '' }}">
                <div class="flex flex-wrap items-center justify-between gap-3"><span class="rounded-full bg-clinic-100 px-3 py-1 text-xs font-bold uppercase text-clinic-700">{{ $entry->entry_status }}</span><time class="text-sm text-slate-500">{{ $entry->recorded_at->format('d-m-Y H:i:s') }} WIB</time></div>
                <p class="mt-4 whitespace-pre-line leading-7 text-slate-800">{{ $entry->content_json['text'] }}</p>
                @if($entry->correction_reason)<p class="mt-4 rounded-xl bg-info/8 p-3 text-sm text-slate-600"><strong>Alasan koreksi:</strong> {{ $entry->correction_reason }}</p>@endif
                @if($entry->diagnoses->isNotEmpty())<ul class="mt-4 text-sm">@foreach($entry->diagnoses as $diagnosis)<li><strong>{{ $diagnosis->diagnosis_code }}</strong> · {{ $diagnosis->diagnosis_name }}</li>@endforeach</ul>@endif
                <p class="mt-5 break-all font-mono text-[11px] text-slate-400">SHA-256 {{ $entry->integrity_hash }}</p>
            </article>
        @endforeach
    </div>
</x-layouts.app>

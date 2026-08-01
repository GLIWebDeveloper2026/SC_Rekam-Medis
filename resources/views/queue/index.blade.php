<x-layouts.app title="Antrean">
    @if (session('status'))<div class="mb-5 rounded-xl bg-success/10 p-4 font-semibold text-success">{{ session('status') }}</div>@endif
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Hari ini · {{ now()->format('d-m-Y') }}</p><h1 class="mt-2 text-3xl font-bold">Antrean pelayanan</h1></div>
        @if (auth()->user()->hasPermission('queue.manage'))<a class="btn-primary" href="{{ route('registrations.create') }}">Buat pendaftaran</a>@endif
    </div>
    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($tickets as $ticket)
            <article class="panel p-5 {{ $ticket->current_priority === 'emergency' ? 'border-danger/40' : ($ticket->current_priority === 'urgent' ? 'border-amber-300' : '') }}">
                <div class="flex items-start justify-between gap-4"><span class="font-heading text-4xl font-bold text-clinic-700">{{ $ticket->queue_number }}</span><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase">{{ $ticket->current_priority }}</span></div>
                <h2 class="mt-4 text-lg font-bold">{{ $ticket->registration->patient->full_name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $ticket->service_type }} · {{ $ticket->status }}</p>
                @if ($ticket->status === 'booked' && auth()->user()->hasPermission('queue.manage'))
                    <form class="mt-4" method="POST" action="{{ route('registrations.check-in', $ticket->registration) }}">@csrf<button class="btn-primary w-full" type="submit">Check-in</button></form>
                @endif
            </article>
        @empty
            <div class="panel p-10 text-center text-slate-500 md:col-span-2 xl:col-span-3">Belum ada antrean hari ini.</div>
        @endforelse
    </div>
</x-layouts.app>

<x-layouts.app title="Pasien">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Master pasien</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Identitas pasien</h1>
            <p class="mt-2 text-slate-500">Cari dengan nama, MRN, atau nomor telepon.</p>
        </div>
        @if (auth()->user()->hasPermission('patients.manage'))
            <a class="btn-primary" href="{{ route('patients.create') }}">Daftarkan pasien</a>
        @endif
    </div>

    <form class="panel mt-8 flex gap-3 p-4" method="GET">
        <label class="sr-only" for="q">Cari pasien</label>
        <input class="form-input" id="q" name="q" value="{{ $query }}" placeholder="Nama, MRN, atau telepon">
        <button class="btn-primary" type="submit">Cari</button>
    </form>

    <div class="panel mt-5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr><th class="px-5 py-4">MRN</th><th class="px-5 py-4">Nama</th><th class="px-5 py-4">Tanggal lahir</th><th class="px-5 py-4">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($patients as $patient)
                        <tr class="hover:bg-clinic-50/50">
                            <td class="px-5 py-4 font-bold text-clinic-700"><a href="{{ route('patients.show', $patient) }}">{{ $patient->medical_record_number }}</a></td>
                            <td class="px-5 py-4 font-semibold text-slate-800">{{ $patient->full_name }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $patient->birth_date->format('d-m-Y') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-clinic-100 px-3 py-1 text-xs font-bold text-clinic-700">{{ $patient->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-10 text-center text-slate-500" colspan="4">Belum ada pasien yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5">{{ $patients->links() }}</div>
</x-layouts.app>

<x-layouts.app title="Pendaftaran Layanan">
    <div class="max-w-3xl"><p class="text-sm font-bold uppercase tracking-[0.2em] text-clinic-600">Pendaftaran & antrean</p><h1 class="mt-2 text-3xl font-bold">Daftarkan layanan pasien</h1>
        <form class="panel mt-8 grid gap-5 p-6 sm:grid-cols-2" method="POST" action="{{ route('registrations.store') }}">@csrf
            <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold">Pasien</label><select class="form-input" name="patient_id" required>@foreach($patients as $patient)<option value="{{ $patient->id }}">{{ $patient->medical_record_number }} · {{ $patient->full_name }}</option>@endforeach</select></div>
            <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold">Jadwal tenaga medis</label><select class="form-input" name="provider_schedule_id" required>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}">{{ $schedule->provider->name }} · {{ $schedule->service_type }} · hari ke-{{ $schedule->day_of_week }}</option>@endforeach</select></div>
            <div><label class="mb-2 block text-sm font-bold">Kanal</label><select class="form-input" name="channel"><option value="front_desk">Meja pendaftaran</option><option value="phone">Telepon</option><option value="other">Lainnya</option></select></div>
            <div><label class="mb-2 block text-sm font-bold">Penjamin</label><select class="form-input" name="payer_type"><option value="general">Umum</option><option value="insurance">Jaminan kesehatan</option><option value="other">Lainnya</option></select></div>
            <div><label class="mb-2 block text-sm font-bold">Layanan</label><select class="form-input" name="requested_service"><option value="general">Dokter umum</option><option value="dental">Dokter gigi</option><option value="nursing">Keperawatan</option></select></div>
            @if($errors->any())<div class="sm:col-span-2 rounded-xl bg-danger/10 p-4 text-danger">{{ $errors->first() }}</div>@endif
            <div class="sm:col-span-2 flex justify-end"><button class="btn-primary" type="submit">Terbitkan nomor antrean</button></div>
        </form>
    </div>
</x-layouts.app>

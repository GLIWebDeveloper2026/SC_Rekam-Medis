<x-layouts.patient title="Portal Pasien">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-clinic-600">Portal pasien</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">Halo, {{ $patient->full_name }}.</h1>
            <p class="mt-2 text-slate-600">Kelola jadwal, check-in, antrean, dan ringkasan kunjungan Anda.</p>
        </div>
        <span class="font-mono text-sm font-bold text-clinic-700">{{ $patient->medical_record_number }}</span>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-clinic-200 bg-clinic-50 p-4 font-semibold text-clinic-800" role="status">{{ session('status') }}</div>
    @endif

    <section class="mt-8 grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <article class="panel p-6" x-data="{
            scheduleId: '{{ old('provider_schedule_id') }}',
            date: '{{ old('appointment_date', now(config('clinic.timezone'))->toDateString()) }}',
            slotStart: '{{ old('slot_start') }}',
            loading: false,
            availableSlots: [],
            availableCount: null,
            statusMessage: '',
            isAvailableDate: true,
            async fetchSlots() {
                if (!this.scheduleId || !this.date) {
                    this.availableSlots = [];
                    this.availableCount = null;
                    this.statusMessage = '';
                    return;
                }
                this.loading = true;
                this.statusMessage = '';
                try {
                    const res = await fetch(`{{ route('patient-portal.slots') }}?provider_schedule_id=${this.scheduleId}&appointment_date=${this.date}`);
                    const data = await res.json();
                    this.isAvailableDate = data.is_available;
                    this.availableCount = data.available_count;
                    this.availableSlots = data.slots || [];
                    if (!data.is_available) {
                        this.statusMessage = data.message;
                    } else if (data.available_count === 0) {
                        this.statusMessage = 'Semua slot jam pada tanggal ini sudah penuh/berlalu.';
                    } else {
                        this.statusMessage = `Tersedia ${data.available_count} slot (kelipatan ${data.slot_duration || 30} menit) pada hari ${data.day_name}, ${data.date_formatted}.`;
                    }
                } catch (e) {
                    this.statusMessage = 'Gagal memuat slot jadwal.';
                } finally {
                    this.loading = false;
                }
            }
        }" x-init="if (scheduleId && date) fetchSlots()">
            <h2 class="text-xl font-bold">Buat janji temu</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Pilih jadwal aktif. Nomor antrean dibuat saat Anda check-in.</p>
            <form class="mt-6 grid gap-4" method="POST" action="{{ route('patient-portal.appointments.store') }}">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-bold" for="provider_schedule_id">Dokter dan layanan</label>
                    <select class="form-input" id="provider_schedule_id" name="provider_schedule_id" x-model="scheduleId" @change="fetchSlots()" required>
                        <option value="">Pilih jadwal</option>
                        @php($dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'])
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->id }}" @selected(old('provider_schedule_id') === $schedule->id)>
                                {{ $schedule->provider->name }} - {{ str($schedule->service_type)->headline() }} ({{ $dayNames[$schedule->day_of_week] ?? 'Hari '.$schedule->day_of_week }}, {{ substr($schedule->start_time, 0, 5) }}-{{ substr($schedule->end_time, 0, 5) }} WIB)
                            </option>
                        @endforeach
                    </select>
                    @error('provider_schedule_id')<p class="mt-2 text-sm font-semibold text-danger">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold" for="appointment_date">Tanggal kunjungan</label>
                        <input class="form-input" id="appointment_date" name="appointment_date" type="date" min="{{ now(config('clinic.timezone'))->toDateString() }}" x-model="date" @change="fetchSlots()" required>
                        @error('appointment_date')<p class="mt-2 text-sm font-semibold text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold" for="slot_start">Jam kunjungan (Kelipatan 30 Menit)</label>
                        <template x-if="availableSlots.length > 0">
                            <select class="form-input" id="slot_start" name="slot_start" x-model="slotStart" required>
                                <option value="">-- Pilih Slot Jam --</option>
                                <template x-for="slot in availableSlots" :key="slot.start">
                                    <option :value="slot.start" x-text="slot.start + ' - ' + slot.end + ' WIB'"></option>
                                </template>
                            </select>
                        </template>
                        <template x-if="availableSlots.length === 0">
                            <input class="form-input" id="slot_start" name="slot_start" type="time" step="1800" x-model="slotStart" placeholder="08:00" required>
                        </template>
                        @error('slot_start')<p class="mt-2 text-sm font-semibold text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div x-show="statusMessage" x-transition class="rounded-lg p-3 text-xs font-semibold" :class="isAvailableDate && availableCount > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger'">
                    <span x-text="statusMessage"></span>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold" for="payer_type">Jenis pembayaran</label>
                    <select class="form-input" id="payer_type" name="payer_type" required>
                        <option value="general" @selected(old('payer_type') === 'general')>Umum</option>
                        <option value="insurance" @selected(old('payer_type') === 'insurance')>Asuransi</option>
                        <option value="other" @selected(old('payer_type') === 'other')>Lainnya</option>
                    </select>
                </div>
                <button class="btn-primary" type="submit">Simpan janji temu</button>
            </form>
        </article>

        <article class="bg-clinic-800 p-6 text-white">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-clinic-200">Antrean hari ini</p>
            @if ($currentQueue)
                <p class="mt-5 font-heading text-6xl font-bold">{{ $currentQueue->queue_number }}</p>
                <p class="mt-3 text-lg font-bold">{{ str($currentQueue->service_type)->headline() }}</p>
                <p class="mt-2 text-clinic-100">Status: {{ str($currentQueue->status)->headline() }}</p>
                <p class="mt-6 text-sm text-clinic-100">Kode booking {{ $currentQueue->registration->booking_code }}</p>
            @else
                <h2 class="mt-5 text-2xl font-bold">Belum ada nomor antrean</h2>
                <p class="mt-3 leading-7 text-clinic-100">Nomor antrean muncul setelah check-in pada tanggal janji temu.</p>
            @endif
        </article>
    </section>

    <section class="mt-8" id="appointments">
        <div class="flex items-end justify-between gap-4">
            <div><h2 class="text-2xl font-bold">Janji temu aktif</h2><p class="mt-2 text-slate-500">Perubahan hanya tersedia untuk jadwal mendatang.</p></div>
        </div>
        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            @forelse ($appointments as $appointment)
                <article class="panel p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-clinic-700">{{ $appointment->schedule->provider->name }}</p>
                            <h3 class="mt-1 text-xl font-bold">{{ str($appointment->schedule->service_type)->headline() }}</h3>
                        </div>
                        <span class="rounded-full bg-clinic-50 px-3 py-1 text-xs font-bold text-clinic-800">{{ str($appointment->status)->headline() }}</span>
                    </div>
                    <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5 text-sm">
                        <div><dt class="text-slate-500">Tanggal</dt><dd class="mt-1 font-bold">{{ $appointment->appointment_date->translatedFormat('d M Y') }}</dd></div>
                        <div><dt class="text-slate-500">Waktu</dt><dd class="mt-1 font-bold">{{ substr($appointment->slot_start, 0, 5) }}-{{ substr($appointment->slot_end, 0, 5) }}</dd></div>
                        <div><dt class="text-slate-500">Kode booking</dt><dd class="mt-1 font-bold">{{ $appointment->registration->booking_code }}</dd></div>
                        <div><dt class="text-slate-500">Pembayaran</dt><dd class="mt-1 font-bold">{{ str($appointment->registration->payer_type)->headline() }}</dd></div>
                    </dl>

                    @if ($appointment->status === \App\Models\Appointment::StatusBooked && $appointment->appointment_date->isToday())
                        <form class="mt-5" method="POST" action="{{ route('patient-portal.appointments.check-in', $appointment) }}">
                            @csrf
                            <button class="btn-primary w-full" type="submit">Check-in sekarang</button>
                        </form>
                    @endif

                    @if ($appointment->status === \App\Models\Appointment::StatusBooked && $appointment->appointment_date->isFuture())
                        <details class="mt-5 border-t border-slate-100 pt-5">
                            <summary class="cursor-pointer font-bold text-clinic-700">Ubah atau batalkan</summary>
                            <form class="mt-4 grid gap-3" method="POST" action="{{ route('patient-portal.appointments.update', $appointment) }}">
                                @csrf
                                @method('PUT')
                                <select class="form-input" name="provider_schedule_id" required>
                                    @foreach ($schedules as $schedule)
                                        <option value="{{ $schedule->id }}" @selected($schedule->id === $appointment->provider_schedule_id)>{{ $schedule->provider->name }} - {{ str($schedule->service_type)->headline() }}</option>
                                    @endforeach
                                </select>
                                <div class="grid gap-3 sm:grid-cols-2"><input class="form-input" name="appointment_date" type="date" min="{{ now(config('clinic.timezone'))->toDateString() }}" value="{{ old('appointment_date', $appointment->appointment_date->toDateString()) }}" required><input class="form-input" name="slot_start" type="time" step="1800" value="{{ old('slot_start', $appointment->slot_start) }}" required></div>
                                <button class="btn-primary" type="submit">Jadwalkan ulang</button>
                            </form>
                            <form class="mt-5 grid gap-3 border-t border-slate-100 pt-5" method="POST" action="{{ route('patient-portal.appointments.destroy', $appointment) }}">
                                @csrf
                                @method('DELETE')
                                <label class="text-sm font-bold" for="cancellation_reason_{{ $appointment->id }}">Alasan pembatalan</label>
                                <textarea class="form-input min-h-24" id="cancellation_reason_{{ $appointment->id }}" name="cancellation_reason" required></textarea>
                                <button class="inline-flex min-h-11 items-center justify-center rounded-[0.625rem] border border-red-200 bg-red-50 px-5 font-bold text-red-700" type="submit">Batalkan janji temu</button>
                            </form>
                        </details>
                    @endif
                </article>
            @empty
                <div class="panel p-8 text-center text-slate-500 lg:col-span-2">Belum ada janji temu aktif.</div>
            @endforelse
        </div>
    </section>

    <section class="mt-8 panel overflow-hidden" id="history">
        <div class="border-b border-slate-100 p-6"><h2 class="text-2xl font-bold">Riwayat kunjungan</h2><p class="mt-2 text-slate-500">Ringkasan administratif tanpa isi catatan klinis.</p></div>
        <div class="divide-y divide-slate-100">
            @forelse ($visits as $visit)
                <article class="grid gap-4 p-6 md:grid-cols-[1fr_auto] md:items-center">
                    <div>
                        <p class="font-bold">{{ $visit->registration->schedule?->provider?->name ?? 'Tenaga medis' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ str($visit->registration->requested_service)->headline() }} - {{ $visit->visit_date->translatedFormat('d M Y') }}</p>
                    </div>
                    <div class="text-sm md:text-right"><p class="font-bold text-clinic-700">{{ str($visit->status)->headline() }}</p><p class="mt-1 text-slate-500">{{ $visit->registration->booking_code }}</p></div>
                </article>
            @empty
                <div class="p-8 text-center text-slate-500">Belum ada riwayat kunjungan.</div>
            @endforelse
        </div>
        @if ($visits->hasPages())<div class="border-t border-slate-100 p-5">{{ $visits->links() }}</div>@endif
    </section>
</x-layouts.patient>

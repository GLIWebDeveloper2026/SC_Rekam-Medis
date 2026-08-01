<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\ProviderSchedule;
use App\Models\Registration;
use App\Models\User;
use App\Services\Appointments\AppointmentAvailability;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookAppointment
{
    public function __construct(private readonly AppointmentAvailability $availability) {}

    public function execute(
        Patient $patient,
        ProviderSchedule $schedule,
        CarbonInterface $date,
        string $slotStart,
        string $payerType,
        User $actor,
        string $source = 'patient_portal',
    ): Appointment {
        return DB::transaction(function () use (
            $patient,
            $schedule,
            $date,
            $slotStart,
            $payerType,
            $actor,
            $source,
        ): Appointment {
            $lockedSchedule = ProviderSchedule::query()->whereKey($schedule)->lockForUpdate()->firstOrFail();

            if (! $lockedSchedule->isAvailableOn($date)) {
                $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                $dayName = $dayNames[$date->isoWeekday()] ?? 'hari tersebut';
                throw ValidationException::withMessages(['appointment_date' => "Dokter/layanan tidak memiliki jadwal praktik pada hari {$dayName} ({$date->format('d-m-Y')}). Pilih tanggal yang sesuai jadwal praktik."]);
            }

            if (! $this->availability->isAvailable($lockedSchedule, $date, $slotStart)) {
                throw ValidationException::withMessages(['slot_start' => 'Slot waktu yang dipilih tidak tersedia (sudah penuh, di luar jam praktik, atau sudah berlalu).']);
            }

            $timezone = config('clinic.timezone');
            $start = CarbonImmutable::parse($date->toDateString().' '.$slotStart, $timezone);
            $end = $start->addMinutes($lockedSchedule->slot_duration_minutes);
            $registration = Registration::query()->create([
                'patient_id' => $patient->id,
                'provider_schedule_id' => $lockedSchedule->id,
                'registration_date' => $date->toDateString(),
                'channel' => $source,
                'payer_type' => $payerType,
                'requested_service' => $lockedSchedule->service_type,
                'status' => 'booked',
                'booking_code' => $this->nextBookingCode($date),
                'created_by' => $actor->id,
            ]);
            $appointment = Appointment::query()->create([
                'registration_id' => $registration->id,
                'provider_schedule_id' => $lockedSchedule->id,
                'appointment_date' => $date->toDateString(),
                'slot_start' => $start->format('H:i:s'),
                'slot_end' => $end->format('H:i:s'),
                'status' => Appointment::StatusBooked,
                'booked_at' => now(),
            ]);

            $appointment->events()->create([
                'event_type' => 'booked',
                'performed_by' => $actor->id,
                'metadata_json' => ['source' => $source],
                'created_at' => now(),
            ]);

            return $appointment->load(['registration', 'schedule.provider']);
        }, attempts: 5);
    }

    private function nextBookingCode(CarbonInterface $date): string
    {
        do {
            $bookingCode = 'BK-'.$date->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Registration::query()->where('booking_code', $bookingCode)->exists());

        return $bookingCode;
    }
}

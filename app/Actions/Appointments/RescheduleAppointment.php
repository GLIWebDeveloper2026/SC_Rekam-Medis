<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\ProviderSchedule;
use App\Models\Registration;
use App\Models\User;
use App\Services\Appointments\AppointmentAvailability;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RescheduleAppointment
{
    public function __construct(private readonly AppointmentAvailability $availability) {}

    public function execute(
        Appointment $appointment,
        ProviderSchedule $targetSchedule,
        CarbonInterface $targetDate,
        string $targetSlotStart,
        User $actor,
    ): Appointment {
        return DB::transaction(function () use (
            $appointment,
            $targetSchedule,
            $targetDate,
            $targetSlotStart,
            $actor,
        ): Appointment {
            $lockedAppointment = Appointment::query()->whereKey($appointment)->lockForUpdate()->firstOrFail();
            $registration = Registration::query()
                ->whereKey($lockedAppointment->registration_id)
                ->lockForUpdate()
                ->firstOrFail();
            $lockedSchedule = ProviderSchedule::query()->whereKey($targetSchedule)->lockForUpdate()->firstOrFail();

            if ($lockedAppointment->status !== Appointment::StatusBooked) {
                throw ValidationException::withMessages(['appointment' => 'Janji temu tidak dapat dijadwalkan ulang.']);
            }

            if ($lockedAppointment->appointment_date->lte(now(config('clinic.timezone'))->startOfDay())) {
                throw ValidationException::withMessages(['appointment' => 'Janji temu hari ini atau yang sudah lewat tidak dapat diubah.']);
            }

            if (! $this->availability->isAvailable(
                $lockedSchedule,
                $targetDate,
                $targetSlotStart,
                $lockedAppointment->id,
            )) {
                throw ValidationException::withMessages(['slot_start' => 'Slot jadwal tujuan tidak tersedia.']);
            }

            $oldSlot = [
                'provider_schedule_id' => $lockedAppointment->provider_schedule_id,
                'appointment_date' => $lockedAppointment->appointment_date->toDateString(),
                'slot_start' => $lockedAppointment->slot_start,
                'slot_end' => $lockedAppointment->slot_end,
            ];
            $start = CarbonImmutable::parse(
                $targetDate->toDateString().' '.$targetSlotStart,
                config('clinic.timezone'),
            );
            $end = $start->addMinutes($lockedSchedule->slot_duration_minutes);

            $lockedAppointment->update([
                'provider_schedule_id' => $lockedSchedule->id,
                'appointment_date' => $targetDate->toDateString(),
                'slot_start' => $start->format('H:i:s'),
                'slot_end' => $end->format('H:i:s'),
            ]);
            $registration->update([
                'provider_schedule_id' => $lockedSchedule->id,
                'registration_date' => $targetDate->toDateString(),
                'requested_service' => $lockedSchedule->service_type,
                'status' => 'booked',
            ]);
            $lockedAppointment->events()->create([
                'event_type' => 'rescheduled',
                'performed_by' => $actor->id,
                'metadata_json' => [
                    'old' => $oldSlot,
                    'new' => [
                        'provider_schedule_id' => $lockedSchedule->id,
                        'appointment_date' => $targetDate->toDateString(),
                        'slot_start' => $start->format('H:i:s'),
                        'slot_end' => $end->format('H:i:s'),
                    ],
                ],
                'created_at' => now(),
            ]);

            return $lockedAppointment->fresh(['registration', 'schedule.provider']);
        }, attempts: 5);
    }
}

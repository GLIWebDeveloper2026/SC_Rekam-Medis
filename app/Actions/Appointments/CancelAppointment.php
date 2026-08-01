<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelAppointment
{
    public function execute(
        Appointment $appointment,
        User $actor,
        string $reason,
    ): Appointment {
        return DB::transaction(function () use ($appointment, $actor, $reason): Appointment {
            $lockedAppointment = Appointment::query()->whereKey($appointment)->lockForUpdate()->firstOrFail();
            $registration = Registration::query()
                ->whereKey($lockedAppointment->registration_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAppointment->status !== Appointment::StatusBooked) {
                throw ValidationException::withMessages(['appointment' => 'Janji temu tidak dapat dibatalkan.']);
            }

            if ($lockedAppointment->appointment_date->lte(now(config('clinic.timezone'))->startOfDay())) {
                throw ValidationException::withMessages(['appointment' => 'Janji temu hari ini atau yang sudah lewat tidak dapat dibatalkan.']);
            }

            $lockedAppointment->update([
                'status' => Appointment::StatusCancelled,
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);
            $registration->update(['status' => 'cancelled']);
            $lockedAppointment->events()->create([
                'event_type' => 'cancelled',
                'performed_by' => $actor->id,
                'metadata_json' => ['reason' => $reason],
                'created_at' => now(),
            ]);

            return $lockedAppointment->fresh(['registration', 'schedule.provider']);
        }, attempts: 5);
    }
}

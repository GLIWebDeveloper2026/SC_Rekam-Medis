<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\QueueTicket;
use App\Models\Registration;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckInAppointment
{
    /**
     * @return array{appointment: Appointment, queue_ticket: QueueTicket, visit: Visit}
     */
    public function execute(Appointment $appointment, User $actor): array
    {
        return DB::transaction(function () use ($appointment, $actor): array {
            $lockedAppointment = Appointment::query()->whereKey($appointment)->lockForUpdate()->firstOrFail();
            $registration = Registration::query()
                ->whereKey($lockedAppointment->registration_id)
                ->lockForUpdate()
                ->firstOrFail();
            $timezone = config('clinic.timezone');
            $now = CarbonImmutable::now($timezone);

            if (! in_array(
                $lockedAppointment->status,
                [Appointment::StatusBooked, Appointment::StatusCheckedIn],
                true,
            )) {
                throw ValidationException::withMessages(['appointment' => 'Janji temu tidak dapat digunakan untuk check-in.']);
            }

            if (! $lockedAppointment->appointment_date->isSameDay($now)) {
                throw ValidationException::withMessages(['appointment' => 'Check-in hanya tersedia pada tanggal janji temu.']);
            }

            $slot = CarbonImmutable::parse(
                $lockedAppointment->appointment_date->toDateString().' '.$lockedAppointment->slot_start,
                $timezone,
            );
            $opensAt = $slot->subMinutes(config('clinic.patient_check_in.opens_minutes_before'));
            $closesAt = $slot->addMinutes(config('clinic.patient_check_in.closes_minutes_after'));

            if ($now->lt($opensAt) || $now->gt($closesAt)) {
                throw ValidationException::withMessages(['appointment' => 'Check-in belum tersedia atau jendelanya sudah berakhir.']);
            }

            $ticket = QueueTicket::query()
                ->where('registration_id', $registration->id)
                ->lockForUpdate()
                ->first();

            if ($ticket === null) {
                $ticket = $this->allocateQueueTicket($registration);
            }

            $previousTicketStatus = $ticket->status;
            $isFirstCheckIn = $lockedAppointment->status !== Appointment::StatusCheckedIn;

            $registration->update(['status' => 'checked_in']);
            $lockedAppointment->update(['status' => Appointment::StatusCheckedIn]);
            $ticket->update([
                'status' => 'waiting',
                'checked_in_at' => $ticket->checked_in_at ?? $now,
            ]);

            if ($previousTicketStatus !== 'waiting') {
                DB::table('queue_events')->insert([
                    'id' => (string) Str::uuid(),
                    'queue_ticket_id' => $ticket->id,
                    'event_type' => 'checked_in',
                    'old_status' => $previousTicketStatus,
                    'new_status' => 'waiting',
                    'old_priority' => $ticket->current_priority,
                    'new_priority' => $ticket->current_priority,
                    'reason' => null,
                    'performed_by' => $actor->id,
                    'created_at' => $now,
                ]);
            }

            if ($isFirstCheckIn) {
                $lockedAppointment->events()->create([
                    'event_type' => 'checked_in',
                    'performed_by' => $actor->id,
                    'metadata_json' => ['queue_ticket_id' => $ticket->id],
                    'created_at' => $now,
                ]);
            }

            $visit = Visit::query()->firstOrCreate(
                ['registration_id' => $registration->id],
                [
                    'patient_id' => $registration->patient_id,
                    'visit_date' => $now->toDateString(),
                    'payer_type' => $registration->payer_type,
                    'status' => 'active',
                    'arrived_at' => $now,
                ],
            );

            return [
                'appointment' => $lockedAppointment->fresh(['registration', 'schedule.provider']),
                'queue_ticket' => $ticket->fresh(),
                'visit' => $visit,
            ];
        }, attempts: 5);
    }

    private function allocateQueueTicket(Registration $registration): QueueTicket
    {
        $serviceDate = $registration->registration_date->toDateString();

        DB::table('daily_queue_counters')->insertOrIgnore([
            'service_date' => $serviceDate,
            'service_type' => $registration->requested_service,
            'last_number' => 0,
            'updated_at' => now(),
        ]);
        $counter = DB::table('daily_queue_counters')
            ->where('service_date', $serviceDate)
            ->where('service_type', $registration->requested_service)
            ->lockForUpdate()
            ->first();
        $queueNumber = ((int) $counter->last_number) + 1;
        DB::table('daily_queue_counters')
            ->where('service_date', $serviceDate)
            ->where('service_type', $registration->requested_service)
            ->update(['last_number' => $queueNumber, 'updated_at' => now()]);

        return QueueTicket::query()->create([
            'registration_id' => $registration->id,
            'service_date' => $serviceDate,
            'service_type' => $registration->requested_service,
            'queue_number' => $queueNumber,
            'original_position' => $queueNumber,
            'current_priority' => 'routine',
            'status' => 'booked',
        ]);
    }
}

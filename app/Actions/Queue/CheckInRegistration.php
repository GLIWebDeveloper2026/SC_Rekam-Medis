<?php

namespace App\Actions\Queue;

use App\Models\Registration;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckInRegistration
{
    public function execute(Registration $registration, string $userId): Visit
    {
        return DB::transaction(function () use ($registration, $userId): Visit {
            $registration = Registration::query()->whereKey($registration)->lockForUpdate()->firstOrFail();
            $ticket = $registration->queueTicket()->lockForUpdate()->firstOrFail();

            $registration->update(['status' => 'checked_in']);
            $ticket->update(['status' => 'waiting', 'checked_in_at' => now()]);
            DB::table('queue_events')->insert([
                'id' => (string) Str::uuid(),
                'queue_ticket_id' => $ticket->id,
                'event_type' => 'checked_in',
                'old_status' => 'booked',
                'new_status' => 'waiting',
                'old_priority' => $ticket->current_priority,
                'new_priority' => $ticket->current_priority,
                'reason' => null,
                'performed_by' => $userId,
                'created_at' => now(),
            ]);

            return Visit::query()->firstOrCreate(
                ['registration_id' => $registration->id],
                [
                    'patient_id' => $registration->patient_id,
                    'visit_date' => now()->toDateString(),
                    'payer_type' => $registration->payer_type,
                    'status' => 'active',
                    'arrived_at' => now(),
                ],
            );
        }, attempts: 5);
    }
}

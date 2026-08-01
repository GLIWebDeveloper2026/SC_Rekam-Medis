<?php

namespace App\Actions\Queue;

use App\Models\ProviderSchedule;
use App\Models\QueueTicket;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterForService
{
    /** @param array<string, mixed> $data */
    public function execute(array $data, string $userId): Registration
    {
        $schedule = ProviderSchedule::query()->findOrFail($data['provider_schedule_id']);

        if (! $schedule->isAvailableOn(now())) {
            throw ValidationException::withMessages(['provider_schedule_id' => 'Dokter tidak memiliki jadwal aktif hari ini.']);
        }

        return DB::transaction(function () use ($data, $userId, $schedule): Registration {
            $registration = Registration::query()->create([
                'patient_id' => $data['patient_id'],
                'provider_schedule_id' => $schedule->id,
                'registration_date' => now()->toDateString(),
                'channel' => $data['channel'],
                'payer_type' => $data['payer_type'],
                'coverage_id' => $data['coverage_id'] ?? null,
                'requested_service' => $data['requested_service'],
                'status' => 'booked',
                'booking_code' => 'BK-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'created_by' => $userId,
            ]);

            DB::table('daily_queue_counters')->insertOrIgnore([
                'service_date' => now()->toDateString(),
                'service_type' => $data['requested_service'],
                'last_number' => 0,
                'updated_at' => now(),
            ]);
            $counter = DB::table('daily_queue_counters')
                ->where('service_date', now()->toDateString())
                ->where('service_type', $data['requested_service'])
                ->lockForUpdate()
                ->first();
            $number = ((int) $counter->last_number) + 1;
            DB::table('daily_queue_counters')
                ->where('service_date', now()->toDateString())
                ->where('service_type', $data['requested_service'])
                ->update(['last_number' => $number, 'updated_at' => now()]);

            QueueTicket::query()->create([
                'registration_id' => $registration->id,
                'service_date' => now()->toDateString(),
                'service_type' => $data['requested_service'],
                'queue_number' => $number,
                'original_position' => $number,
                'current_priority' => 'routine',
                'status' => 'booked',
            ]);

            return $registration;
        }, attempts: 5);
    }
}

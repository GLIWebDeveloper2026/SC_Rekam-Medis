<?php

namespace Database\Seeders;

use App\Models\ProviderSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClinicScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['dokter@sehatbersama.test', 'general', range(1, 6), '07:00:00', '21:00:00'],
            ['doktergigi@sehatbersama.test', 'dental', [1, 3, 6], '09:00:00', '16:00:00'],
            ['perawat@sehatbersama.test', 'nursing', range(1, 6), '07:00:00', '21:00:00'],
        ];

        foreach ($providers as [$email, $service, $days, $start, $end]) {
            $providerId = User::query()->where('email', $email)->valueOrFail('id');

            foreach ($days as $day) {
                ProviderSchedule::query()->updateOrCreate(
                    [
                        'provider_user_id' => $providerId,
                        'service_type' => $service,
                        'day_of_week' => $day,
                        'start_time' => $start,
                    ],
                    [
                        'end_time' => $end,
                        'slot_duration_minutes' => 30,
                        'slot_capacity' => 1,
                        'effective_from' => now()->startOfYear()->toDateString(),
                        'effective_until' => null,
                        'status' => 'active',
                    ],
                );
            }
        }
    }
}

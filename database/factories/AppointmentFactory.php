<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\ProviderSchedule;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $appointmentDate = now()->addWeek()->startOfDay();

        return [
            'appointment_date' => $appointmentDate->toDateString(),
            'slot_start' => '09:00:00',
            'slot_end' => '09:30:00',
            'status' => Appointment::StatusBooked,
            'booked_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Appointment $appointment): void {
            if ($appointment->registration_id !== null && $appointment->provider_schedule_id !== null) {
                return;
            }

            $actor = User::factory()->create();
            $provider = User::factory()->create();
            $appointmentDate = $appointment->appointment_date;
            $schedule = ProviderSchedule::query()->create([
                'provider_user_id' => $provider->id,
                'service_type' => 'general',
                'day_of_week' => $appointmentDate->isoWeekday(),
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'slot_duration_minutes' => 30,
                'slot_capacity' => 1,
                'effective_from' => now()->subMonth()->toDateString(),
                'status' => 'active',
            ]);
            $patient = Patient::factory()->create(['created_by' => $actor->id]);
            $registration = Registration::query()->create([
                'patient_id' => $patient->id,
                'provider_schedule_id' => $schedule->id,
                'registration_date' => $appointmentDate->toDateString(),
                'channel' => 'patient_portal',
                'payer_type' => 'general',
                'requested_service' => 'general',
                'status' => 'booked',
                'booking_code' => 'BK-'.$appointmentDate->format('Ymd').'-'.Str::upper(Str::random(6)),
                'created_by' => $actor->id,
            ]);

            $appointment->registration_id = $registration->id;
            $appointment->provider_schedule_id = $schedule->id;
        });
    }
}

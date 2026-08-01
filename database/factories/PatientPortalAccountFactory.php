<?php

namespace Database\Factories;

use App\Models\PatientPortalAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientPortalAccount>
 */
class PatientPortalAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => PatientPortalAccount::StatusPending,
            'claimed_birth_date' => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'claimed_phone' => fake()->numerify('08##########'),
            'claimed_medical_record_number' => null,
            'claimed_identifier_hash' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => PatientPortalAccount::StatusPending]);
    }
}

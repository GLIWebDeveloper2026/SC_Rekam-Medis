<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'medical_record_number' => 'RM-'.fake()->unique()->numerify('##########'),
            'full_name' => $name,
            'normalized_name' => str($name)->lower()->squish()->toString(),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'sex' => fake()->randomElement(['female', 'male']),
            'phone' => fake()->numerify('08##########'),
            'status' => 'active',
            'created_by' => User::factory(),
        ];
    }
}

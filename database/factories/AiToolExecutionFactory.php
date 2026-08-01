<?php

namespace Database\Factories;

use App\Models\AiToolExecution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiToolExecution>
 */
class AiToolExecutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idempotency_key' => fake()->uuid(),
            'user_id' => User::factory(),
            'active_role' => 'patient',
            'tool_name' => 'list_public_schedules',
            'request_fingerprint' => hash('sha256', fake()->uuid()),
            'status' => AiToolExecution::StatusSucceeded,
            'safe_input_json' => [],
            'safe_output_json' => ['ok' => true],
            'started_at' => now(),
            'completed_at' => now(),
            'expires_at' => now()->addDay(),
        ];
    }
}

<?php

namespace Tests\Feature\Queue;

use App\Models\Patient;
use App\Models\ProviderSchedule;
use App\Models\QueueTicket;
use App\Models\Registration;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueueWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_numbers_are_unique_and_increment_per_service_day(): void
    {
        [$staff, $patient, $schedule] = $this->context();

        $payload = [
            'patient_id' => $patient->id,
            'provider_schedule_id' => $schedule->id,
            'channel' => 'front_desk',
            'payer_type' => 'general',
            'requested_service' => 'general',
        ];

        $this->actingAs($staff)->post('/registrations', $payload)->assertSessionHasNoErrors();
        $this->actingAs($staff)->post('/registrations', $payload)->assertSessionHasNoErrors();

        $this->assertSame([1, 2], QueueTicket::query()->orderBy('queue_number')->pluck('queue_number')->all());
        $this->assertSame(2, QueueTicket::query()->distinct()->count('queue_number'));
    }

    public function test_check_in_creates_one_visit_and_priority_override_keeps_original_number(): void
    {
        [$staff, $patient, $schedule] = $this->context();
        DB::table('daily_queue_counters')->insert([
            'service_date' => now()->toDateString(),
            'service_type' => 'general',
            'last_number' => 22,
            'updated_at' => now(),
        ]);

        $this->actingAs($staff)->post('/registrations', [
            'patient_id' => $patient->id,
            'provider_schedule_id' => $schedule->id,
            'channel' => 'front_desk',
            'payer_type' => 'general',
            'requested_service' => 'general',
        ])->assertSessionHasNoErrors();

        $registration = Registration::query()->sole();
        $ticket = QueueTicket::query()->sole();
        $this->assertSame(23, $ticket->queue_number);

        $this->actingAs($staff)
            ->post("/registrations/{$registration->id}/check-in")
            ->assertSessionHasNoErrors();

        $nurse = User::query()->where('email', 'perawat@sehatbersama.test')->firstOrFail();
        $this->actingAs($nurse)->post("/queue-tickets/{$ticket->id}/triage", [
            'chief_complaint' => 'Luka terbuka pada lengan kanan',
            'priority_level' => 'urgent',
            'priority_reason' => 'Perdarahan aktif dan membutuhkan penanganan segera.',
            'temperature' => 36.8,
            'pulse' => 102,
        ])->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(23, $ticket->queue_number);
        $this->assertSame(23, $ticket->original_position);
        $this->assertSame('urgent', $ticket->current_priority);
        $this->assertSame(1, Visit::query()->count());
        $this->assertDatabaseHas('queue_events', [
            'queue_ticket_id' => $ticket->id,
            'event_type' => 'priority_overridden',
            'new_priority' => 'urgent',
            'performed_by' => $nurse->id,
        ]);
    }

    /** @return array{User, Patient, ProviderSchedule} */
    private function context(): array
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create([
            'medical_record_number' => 'RM-202608-000001',
            'full_name' => 'Pasien Antrean',
            'birth_date' => '1992-05-10',
            'sex' => 'female',
            'status' => 'active',
            'created_by' => $staff->id,
        ]);
        $schedule = ProviderSchedule::query()->create([
            'provider_user_id' => $doctor->id,
            'service_type' => 'general',
            'day_of_week' => now()->isoWeekday(),
            'start_time' => '00:00:00',
            'end_time' => '23:59:59',
            'effective_from' => now()->subDay()->toDateString(),
            'effective_until' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        return [$staff, $patient, $schedule];
    }
}

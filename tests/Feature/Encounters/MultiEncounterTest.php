<?php

namespace Tests\Feature\Encounters;

use App\Models\Encounter;
use App\Models\Patient;
use App\Models\ProviderSchedule;
use App\Models\Registration;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiEncounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_visit_can_have_general_and_dental_encounters(): void
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $dentist = User::query()->where('email', 'doktergigi@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create([
            'medical_record_number' => 'RM-202608-000001',
            'full_name' => 'Pasien Multi Layanan',
            'birth_date' => '1984-06-01',
            'sex' => 'male',
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
            'status' => 'active',
        ]);

        $this->actingAs($staff)->post('/registrations', [
            'patient_id' => $patient->id,
            'provider_schedule_id' => $schedule->id,
            'channel' => 'phone',
            'payer_type' => 'insurance',
            'requested_service' => 'general',
        ]);
        $registration = Registration::query()->sole();
        $this->actingAs($staff)->post("/registrations/{$registration->id}/check-in");
        $visit = Visit::query()->sole();

        $this->actingAs($doctor)->post("/visits/{$visit->id}/encounters", [
            'service_type' => 'general',
            'responsible_provider_id' => $doctor->id,
        ])->assertSessionHasNoErrors();
        $general = Encounter::query()->sole();
        $this->actingAs($doctor)->post("/visits/{$visit->id}/encounters", [
            'service_type' => 'dental',
            'responsible_provider_id' => $dentist->id,
            'referral_from_encounter_id' => $general->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Visit::query()->count());
        $this->assertSame(2, Encounter::query()->count());
        $this->assertDatabaseHas('encounters', ['visit_id' => $visit->id, 'service_type' => 'general']);
        $this->assertDatabaseHas('encounters', ['visit_id' => $visit->id, 'service_type' => 'dental']);
    }
}

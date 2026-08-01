<?php

namespace Tests\Feature\Patients;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_baby_without_nik_can_be_registered_with_guardian(): void
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();

        $response = $this->actingAs($staff)->post('/patients', [
            'full_name' => 'Bayi Ny. Sari',
            'birth_date' => now()->subMonths(2)->toDateString(),
            'sex' => 'female',
            'phone' => '081234567890',
            'guardian_name' => 'Sari Wulandari',
            'guardian_relationship' => 'ibu',
            'guardian_phone' => '081234567890',
        ]);

        $patient = Patient::query()->sole();

        $response->assertRedirect(route('patients.show', $patient));
        $this->assertStringStartsWith('RM-', $patient->medical_record_number);
        $this->assertDatabaseHas('patient_guardians', [
            'patient_id' => $patient->id,
            'guardian_name' => 'Sari Wulandari',
        ]);
        $this->assertDatabaseHas('patient_identifiers', [
            'patient_id' => $patient->id,
            'identifier_type' => 'temporary',
        ]);
    }

    public function test_nik_can_be_added_later_without_creating_another_patient(): void
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create([
            'medical_record_number' => 'RM-202608-000001',
            'full_name' => 'Bayi Ny. Sari',
            'birth_date' => now()->subMonths(5)->toDateString(),
            'sex' => 'female',
            'status' => 'active',
            'created_by' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->post("/patients/{$patient->id}/identifiers", [
                'identifier_type' => 'nik',
                'identifier_value' => '3273014401260001',
                'verified_status' => 'verified',
            ])
            ->assertRedirect(route('patients.show', $patient));

        $this->assertSame(1, Patient::query()->count());
        $this->assertDatabaseHas('patient_identifiers', [
            'patient_id' => $patient->id,
            'identifier_type' => 'nik',
            'verified_status' => 'verified',
        ]);
    }

    public function test_same_active_nik_cannot_be_attached_to_two_patients(): void
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $first = $this->patient($staff, 'RM-202608-000001', 'Siti Pertama');
        $second = $this->patient($staff, 'RM-202608-000002', 'Siti Kedua');

        $payload = [
            'identifier_type' => 'nik',
            'identifier_value' => '3273014401260001',
            'verified_status' => 'verified',
        ];

        $this->actingAs($staff)->post("/patients/{$first->id}/identifiers", $payload)->assertSessionHasNoErrors();
        $this->actingAs($staff)->post("/patients/{$second->id}/identifiers", $payload)->assertSessionHasErrors('identifier_value');
    }

    public function test_registration_staff_only_sees_allergy_safety_indicator(): void
    {
        $this->seed(ClinicSeeder::class);
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $patient = $this->patient($staff, 'RM-202608-000001', 'Siti Alergi');

        $this->actingAs($doctor)->post("/patients/{$patient->id}/allergies", [
            'substance_name' => 'Amoksisilin',
            'reaction' => 'Sesak napas berat',
            'severity' => 'severe',
            'clinical_status' => 'active',
            'source' => 'Pasien',
        ])->assertSessionHasNoErrors();

        $this->actingAs($staff)
            ->get("/patients/{$patient->id}")
            ->assertOk()
            ->assertSee('Memiliki alergi obat')
            ->assertDontSee('Sesak napas berat');
    }

    private function patient(User $creator, string $mrn, string $name): Patient
    {
        return Patient::query()->create([
            'medical_record_number' => $mrn,
            'full_name' => $name,
            'birth_date' => '1990-01-01',
            'sex' => 'female',
            'status' => 'active',
            'created_by' => $creator->id,
        ]);
    }
}

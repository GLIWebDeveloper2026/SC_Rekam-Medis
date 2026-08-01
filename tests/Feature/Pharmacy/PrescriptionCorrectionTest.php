<?php

namespace Tests\Feature\Pharmacy;

use App\Models\AllergyEntry;
use App\Models\Encounter;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Registration;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_allergy_must_be_acknowledged_before_final_prescription(): void
    {
        [$doctor, $patient, $encounter, $medicine] = $this->context();
        $payload = [
            'patient_id' => $patient->id, 'substance_name' => $medicine->generic_name,
            'reaction' => 'Urtikaria', 'severity' => 'moderate', 'clinical_status' => 'active',
            'verification_status' => 'confirmed', 'source' => 'Pasien', 'recorded_by' => $doctor->id,
            'recorded_at' => now(),
        ];
        AllergyEntry::query()->create([...$payload, 'integrity_hash' => hash('sha256', json_encode($payload))]);

        $prescription = $this->prescriptionPayload($medicine);
        $this->actingAs($doctor)
            ->post("/encounters/{$encounter->id}/prescriptions", $prescription)
            ->assertSessionHasErrors('allergy_acknowledged');

        $this->actingAs($doctor)
            ->post("/encounters/{$encounter->id}/prescriptions", [...$prescription, 'allergy_acknowledged' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Prescription::query()->count());
    }

    public function test_correction_creates_a_new_prescription_and_keeps_0915_and_1140_timeline(): void
    {
        [$doctor, , $encounter, $medicine] = $this->context();
        Carbon::setTestNow('2026-08-01 09:15:00', 'Asia/Jakarta');
        $this->actingAs($doctor)->post("/encounters/{$encounter->id}/prescriptions", [
            ...$this->prescriptionPayload($medicine),
            'allergy_acknowledged' => '1',
        ]);
        $original = Prescription::query()->sole();

        Carbon::setTestNow('2026-08-01 11:40:00', 'Asia/Jakarta');
        $this->actingAs($doctor)->post("/prescriptions/{$original->id}/corrections", [
            'dosage' => '1 tablet',
            'frequency' => '2 kali sehari',
            'route' => 'oral',
            'duration' => '5 hari',
            'quantity' => 10,
            'instruction' => 'Sesudah makan',
            'correction_reason' => 'Frekuensi pada resep awal tertulis tiga kali sehari.',
            'dispensing_impact' => 'already_dispensed',
        ])->assertSessionHasNoErrors();

        $correction = Prescription::query()->whereNot('id', $original->id)->sole();
        $this->assertSame($original->id, $correction->corrects_prescription_id);
        $this->assertSame('09:15:00', $original->finalized_at->format('H:i:s'));
        $this->assertSame('11:40:00', $correction->finalized_at->format('H:i:s'));
        $this->assertSame('3 kali sehari', $original->items()->sole()->frequency);
        $this->assertSame('2 kali sehari', $correction->items()->sole()->frequency);
        $this->assertDatabaseHas('prescription_events', [
            'prescription_id' => $original->id,
            'event_type' => 'corrected',
        ]);
    }

    /** @return array{User, Patient, Encounter, Medicine} */
    private function context(): array
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create([
            'medical_record_number' => 'RM-RX-001', 'full_name' => 'Pasien Resep', 'birth_date' => '1988-01-01',
            'sex' => 'female', 'status' => 'active', 'created_by' => $staff->id,
        ]);
        $registration = Registration::query()->create([
            'patient_id' => $patient->id, 'registration_date' => now()->toDateString(), 'channel' => 'front_desk',
            'payer_type' => 'general', 'requested_service' => 'general', 'status' => 'checked_in',
            'booking_code' => 'BK-RX-001', 'created_by' => $staff->id,
        ]);
        $visit = Visit::query()->create([
            'patient_id' => $patient->id, 'registration_id' => $registration->id, 'visit_date' => now()->toDateString(),
            'payer_type' => 'general', 'status' => 'active', 'arrived_at' => now(),
        ]);
        $encounter = Encounter::query()->create([
            'visit_id' => $visit->id, 'service_type' => 'general', 'responsible_provider_id' => $doctor->id,
            'status' => 'active', 'started_at' => now(),
        ]);
        $medicine = Medicine::query()->create([
            'code' => 'MED-001', 'generic_name' => 'Amoksisilin', 'dosage_form' => 'tablet',
            'strength' => '500 mg', 'unit' => 'tablet', 'status' => 'active',
        ]);

        return [$doctor, $patient, $encounter, $medicine];
    }

    /** @return array<string, mixed> */
    private function prescriptionPayload(Medicine $medicine): array
    {
        return [
            'medicine_id' => $medicine->id,
            'dosage' => '1 tablet',
            'frequency' => '3 kali sehari',
            'route' => 'oral',
            'duration' => '5 hari',
            'quantity' => 15,
            'instruction' => 'Sesudah makan',
            'preparation_type' => 'finished',
        ];
    }
}

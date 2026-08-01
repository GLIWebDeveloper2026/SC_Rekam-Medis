<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Encounter;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Registration;
use App\Models\SubstitutionRequest;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubstitutionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_verbal_approval_is_preserved_and_then_ratified_digitally(): void
    {
        [$doctor, $pharmacist, $item, $replacement] = $this->context();

        $this->actingAs($pharmacist)->post("/prescription-items/{$item->id}/substitutions", [
            'proposed_medicine_id' => $replacement->id,
            'reason' => 'Stok obat merek awal kosong; zat aktif dan kekuatan setara tersedia.',
        ])->assertSessionHasNoErrors();
        $request = SubstitutionRequest::query()->sole();

        $this->actingAs($pharmacist)->post("/substitution-requests/{$request->id}/verbal-approval", [
            'doctor_id' => $doctor->id,
            'communication_channel' => 'phone',
            'verbal_approval_at' => now()->format('Y-m-d\TH:i'),
            'notes' => 'Dokter menyetujui substitusi dengan dosis dan aturan pakai yang sama.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('verbal_approved_pending_ratification', $request->refresh()->status);
        $this->actingAs($doctor)
            ->post("/substitution-requests/{$request->id}/ratify", ['confirmation' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame('ratified', $request->refresh()->status);
        $this->assertDatabaseHas('substitution_events', ['substitution_request_id' => $request->id, 'event_type' => 'verbal_approved']);
        $this->assertDatabaseHas('substitution_events', ['substitution_request_id' => $request->id, 'event_type' => 'ratified']);
    }

    /** @return array{User, User, PrescriptionItem, Medicine} */
    private function context(): array
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $pharmacist = User::query()->where('email', 'apoteker@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create([
            'medical_record_number' => 'RM-SUB-001', 'full_name' => 'Pasien Substitusi', 'birth_date' => '1980-01-01',
            'sex' => 'male', 'status' => 'active', 'created_by' => $staff->id,
        ]);
        $registration = Registration::query()->create([
            'patient_id' => $patient->id, 'registration_date' => now()->toDateString(), 'channel' => 'front_desk',
            'payer_type' => 'general', 'requested_service' => 'general', 'status' => 'checked_in',
            'booking_code' => 'BK-SUB-001', 'created_by' => $staff->id,
        ]);
        $visit = Visit::query()->create([
            'patient_id' => $patient->id, 'registration_id' => $registration->id, 'visit_date' => now()->toDateString(),
            'payer_type' => 'general', 'status' => 'active', 'arrived_at' => now(),
        ]);
        $encounter = Encounter::query()->create([
            'visit_id' => $visit->id, 'service_type' => 'general', 'responsible_provider_id' => $doctor->id,
            'status' => 'finalized', 'started_at' => now(), 'finalized_at' => now(),
        ]);
        $original = Medicine::query()->create(['code' => 'MED-A', 'generic_name' => 'Paracetamol A', 'dosage_form' => 'tablet', 'strength' => '500 mg', 'unit' => 'tablet', 'status' => 'active']);
        $replacement = Medicine::query()->create(['code' => 'MED-B', 'generic_name' => 'Paracetamol B', 'dosage_form' => 'tablet', 'strength' => '500 mg', 'unit' => 'tablet', 'status' => 'active']);
        $prescription = Prescription::query()->create([
            'patient_id' => $patient->id, 'visit_id' => $visit->id, 'encounter_id' => $encounter->id,
            'prescriber_id' => $doctor->id, 'status' => 'finalized', 'finalized_at' => now(), 'integrity_hash' => str_repeat('b', 64),
        ]);
        $item = PrescriptionItem::query()->create([
            'prescription_id' => $prescription->id, 'medicine_id' => $original->id, 'medicine_name_snapshot' => $original->generic_name,
            'strength_snapshot' => $original->strength, 'dosage' => '1 tablet', 'frequency' => '3 kali sehari',
            'route' => 'oral', 'duration' => '3 hari', 'quantity' => 9, 'instruction' => 'Sesudah makan',
            'preparation_type' => 'finished', 'integrity_hash' => str_repeat('c', 64),
        ]);

        return [$doctor, $pharmacist, $item, $replacement];
    }
}

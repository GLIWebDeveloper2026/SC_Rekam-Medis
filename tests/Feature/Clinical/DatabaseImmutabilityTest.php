<?php

namespace Tests\Feature\Clinical;

use App\Models\ClinicalEntry;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Registration;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_clinical_entry_cannot_be_updated_or_deleted_by_model(): void
    {
        $entry = $this->entry();

        try {
            $entry->update(['correction_reason' => 'ubah paksa']);
            $this->fail('Update clinical entry seharusnya ditolak.');
        } catch (\LogicException) {
            $this->assertDatabaseHas('clinical_entries', ['id' => $entry->id, 'correction_reason' => null]);
        }

        $this->expectException(\LogicException::class);
        $entry->delete();
    }

    public function test_edit_and_delete_routes_do_not_exist(): void
    {
        $entry = $this->entry();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();

        $this->actingAs($doctor)->put("/clinical-entries/{$entry->id}", [])->assertMethodNotAllowed();
        $this->actingAs($doctor)->delete("/clinical-entries/{$entry->id}")->assertMethodNotAllowed();
    }

    private function entry(): ClinicalEntry
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create([
            'medical_record_number' => 'RM-IMMUTABLE-001', 'full_name' => 'Pasien Immutable',
            'birth_date' => '1980-01-01', 'sex' => 'male', 'status' => 'active', 'created_by' => $staff->id,
        ]);
        $registration = Registration::query()->create([
            'patient_id' => $patient->id, 'registration_date' => now()->toDateString(), 'channel' => 'front_desk',
            'payer_type' => 'general', 'requested_service' => 'general', 'status' => 'checked_in',
            'booking_code' => 'BK-IMMUTABLE', 'created_by' => $staff->id,
        ]);
        $visit = Visit::query()->create([
            'patient_id' => $patient->id, 'registration_id' => $registration->id, 'visit_date' => now()->toDateString(),
            'payer_type' => 'general', 'status' => 'active', 'arrived_at' => now(),
        ]);
        $encounter = Encounter::query()->create([
            'visit_id' => $visit->id, 'service_type' => 'general', 'responsible_provider_id' => $doctor->id,
            'status' => 'finalized', 'started_at' => now(), 'finalized_at' => now(),
        ]);

        return ClinicalEntry::query()->create([
            'patient_id' => $patient->id, 'visit_id' => $visit->id, 'encounter_id' => $encounter->id,
            'entry_type' => 'assessment', 'content_json' => ['text' => 'Final'], 'author_id' => $doctor->id,
            'author_role' => 'doctor', 'clinical_time' => now(), 'recorded_at' => now(), 'finalized_at' => now(),
            'entry_status' => 'original', 'integrity_hash' => str_repeat('a', 64),
        ]);
    }
}

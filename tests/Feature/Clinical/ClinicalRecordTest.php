<?php

namespace Tests\Feature\Clinical;

use App\Models\ClinicalDraft;
use App\Models\ClinicalEntry;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Registration;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_is_finalized_into_immutable_entry_with_diagnosis_and_hash(): void
    {
        [$doctor, $encounter] = $this->context();

        $this->actingAs($doctor)->post("/encounters/{$encounter->id}/clinical-drafts", [
            'entry_type' => 'assessment',
            'content' => 'Demam tiga hari, batuk, tidak ada sesak.',
        ])->assertSessionHasNoErrors();
        $draft = ClinicalDraft::query()->sole();

        $this->actingAs($doctor)->post("/clinical-drafts/{$draft->id}/finalize", [
            'clinical_time' => now()->format('Y-m-d\TH:i'),
            'diagnosis_code' => 'J06.9',
            'diagnosis_name' => 'Infeksi saluran pernapasan akut',
            'diagnosis_type' => 'primary',
            'is_primary' => true,
            'confirmation' => '1',
        ])->assertRedirect();

        $entry = ClinicalEntry::query()->sole();
        $this->assertSame('finalized', $draft->refresh()->status);
        $this->assertSame('finalized', $encounter->refresh()->status);
        $this->assertSame(64, strlen($entry->integrity_hash));
        $this->assertDatabaseHas('diagnosis_entries', [
            'clinical_entry_id' => $entry->id,
            'diagnosis_code' => 'J06.9',
            'is_primary' => true,
        ]);
    }

    public function test_addendum_keeps_original_content_unchanged(): void
    {
        [$doctor, $encounter] = $this->context();
        $original = $this->finalEntry($doctor, $encounter, 'Dosis awal ditulis satu tablet tiga kali sehari.');

        $this->actingAs($doctor)->post("/clinical-entries/{$original->id}/addenda", [
            'content' => 'Koreksi: dosis yang benar satu tablet dua kali sehari.',
            'correction_reason' => 'Kesalahan penulisan frekuensi dosis ditemukan saat peninjauan.',
            'clinical_time' => now()->format('Y-m-d\TH:i'),
            'confirmation' => '1',
        ])->assertRedirect();

        $this->assertSame('Dosis awal ditulis satu tablet tiga kali sehari.', $original->fresh()->content_json['text']);
        $this->assertDatabaseHas('clinical_entries', [
            'supersedes_entry_id' => $original->id,
            'entry_status' => 'addendum',
        ]);
        $this->assertSame(2, ClinicalEntry::query()->count());
    }

    public function test_registration_staff_cannot_read_final_clinical_entry(): void
    {
        [$doctor, $encounter] = $this->context();
        $entry = $this->finalEntry($doctor, $encounter, 'Catatan diagnosis rahasia.');
        $registration = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();

        $this->actingAs($registration)
            ->get("/clinical-entries/{$entry->id}")
            ->assertForbidden()
            ->assertDontSee('Catatan diagnosis rahasia.');
    }

    private function finalEntry(User $doctor, Encounter $encounter, string $content): ClinicalEntry
    {
        $this->actingAs($doctor)->post("/encounters/{$encounter->id}/clinical-drafts", [
            'entry_type' => 'assessment',
            'content' => $content,
        ]);
        $draft = ClinicalDraft::query()->latest()->firstOrFail();
        $this->actingAs($doctor)->post("/clinical-drafts/{$draft->id}/finalize", [
            'clinical_time' => now()->format('Y-m-d\TH:i'),
            'confirmation' => '1',
        ]);

        return ClinicalEntry::query()->latest('recorded_at')->firstOrFail();
    }

    /** @return array{User, Encounter} */
    private function context(): array
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create([
            'medical_record_number' => 'RM-202608-000001', 'full_name' => 'Pasien Klinis',
            'birth_date' => '1990-02-02', 'sex' => 'female', 'status' => 'active', 'created_by' => $staff->id,
        ]);
        $registration = Registration::query()->create([
            'patient_id' => $patient->id, 'registration_date' => now()->toDateString(), 'channel' => 'front_desk',
            'payer_type' => 'general', 'requested_service' => 'general', 'status' => 'checked_in',
            'booking_code' => 'BK-CLINICAL-001', 'created_by' => $staff->id,
        ]);
        $visit = Visit::query()->create([
            'patient_id' => $patient->id, 'registration_id' => $registration->id, 'visit_date' => now()->toDateString(),
            'payer_type' => 'general', 'status' => 'active', 'arrived_at' => now(),
        ]);
        $encounter = Encounter::query()->create([
            'visit_id' => $visit->id, 'service_type' => 'general', 'responsible_provider_id' => $doctor->id,
            'status' => 'active', 'started_at' => now(),
        ]);

        return [$doctor, $encounter];
    }
}

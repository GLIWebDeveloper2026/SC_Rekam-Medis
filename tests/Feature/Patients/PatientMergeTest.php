<?php

namespace Tests\Feature\Patients;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_patients_are_logically_merged_without_deleting_history(): void
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $approver = User::query()->where('email', 'owner@sehatbersama.test')->firstOrFail();
        $canonical = $this->patient($staff, 'RM-202308-000021', 'Siti Aminah');
        $source = $this->patient($staff, 'RM-202608-000144', 'Siti Aminah S.');

        $this->actingAs($staff)->post('/patient-merges', [
            'canonical_patient_id' => $canonical->id,
            'source_patient_id' => $source->id,
            'reason' => 'Nama, tanggal lahir, telepon, dan dokumen identitas terverifikasi sama.',
            'approved_by' => $approver->id,
        ])->assertRedirect(route('patients.show', $canonical));

        $this->assertSame(2, Patient::query()->count());
        $this->assertSame($canonical->id, $source->refresh()->canonical_patient_id);
        $this->assertSame('merged', $source->status);
        $this->assertDatabaseHas('patient_merge_events', [
            'canonical_patient_id' => $canonical->id,
            'source_patient_id' => $source->id,
            'event_type' => 'merged',
            'performed_by' => $staff->id,
            'approved_by' => $approver->id,
        ]);
    }

    private function patient(User $creator, string $mrn, string $name): Patient
    {
        return Patient::query()->create([
            'medical_record_number' => $mrn,
            'full_name' => $name,
            'birth_date' => '1988-04-12',
            'sex' => 'female',
            'phone' => '081299991111',
            'status' => 'active',
            'created_by' => $creator->id,
        ]);
    }
}

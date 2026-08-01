<?php

namespace Tests\Feature\Documents;

use App\Models\GeneratedDocument;
use App\Models\MedicalRecordCopyRequest;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MedicalRecordCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_copy_document_requires_identity_verification_and_approval_before_generation(): void
    {
        Storage::fake('private');
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create([
            'medical_record_number' => 'RM-COPY-001', 'full_name' => 'Pasien Salinan', 'birth_date' => '1979-03-01',
            'sex' => 'female', 'status' => 'active', 'created_by' => $staff->id,
        ]);

        $this->actingAs($staff)->post('/medical-record-copy-requests', [
            'patient_id' => $patient->id, 'requester_name' => 'Pasien Salinan', 'requester_relationship' => 'self',
            'purpose' => 'Klaim asuransi kesehatan', 'requested_period_start' => '2026-01-01',
            'requested_period_end' => '2026-08-01', 'requested_scope' => 'Ringkasan pemeriksaan dan diagnosis',
        ])->assertSessionHasNoErrors();
        $copyRequest = MedicalRecordCopyRequest::query()->sole();

        $this->actingAs($staff)
            ->post("/medical-record-copy-requests/{$copyRequest->id}/generate")
            ->assertSessionHasErrors('status');

        $this->actingAs($doctor)->post("/medical-record-copy-requests/{$copyRequest->id}/approve", [
            'identity_verified' => '1', 'approval_notes' => 'Identitas pasien cocok dengan dokumen asli.',
        ])->assertRedirect(route('record-copies.index'))->assertSessionHasNoErrors();
        $this->actingAs($staff)
            ->post("/medical-record-copy-requests/{$copyRequest->id}/generate")
            ->assertRedirect(route('record-copies.index'))
            ->assertSessionHasNoErrors();

        $document = GeneratedDocument::query()->sole();
        Storage::disk('private')->assertExists($document->storage_key);
        $this->assertSame(hash('sha256', Storage::disk('private')->get($document->storage_key)), $document->checksum);
        $this->assertStringContainsString('SALINAN TERKONTROL', $document->watermark_text);
    }
}

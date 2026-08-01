<?php

namespace Tests\Feature\Reports;

use App\Models\ClinicalEntry;
use App\Models\DiagnosisEntry;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Registration;
use App\Models\User;
use App\Models\Visit;
use App\Services\ClinicReport;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_filter_keeps_the_requested_inclusive_end_date(): void
    {
        $this->seed(ClinicSeeder::class);
        $owner = User::query()->where('email', 'owner@sehatbersama.test')->firstOrFail();

        $response = $this->actingAs($owner)->get(route('reports.index', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertSee('value="2026-07-31"', false);
    }

    public function test_report_separates_visits_encounters_and_canonical_unique_patients(): void
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $canonical = $this->patient($staff, 'RM-REPORT-001', 'Siti Aminah');
        $source = $this->patient($staff, 'RM-REPORT-002', 'Siti Aminah S.', $canonical->id, 'merged');

        $firstVisit = $this->visit($staff, $canonical, 'BK-REPORT-001');
        $secondVisit = $this->visit($staff, $source, 'BK-REPORT-002');
        $encounters = [
            $this->encounter($doctor, $firstVisit, 'general'),
            $this->encounter($doctor, $firstVisit, 'dental'),
            $this->encounter($doctor, $secondVisit, 'general'),
        ];

        foreach ($encounters as $index => $encounter) {
            $entry = ClinicalEntry::query()->create([
                'patient_id' => $encounter->visit->patient_id, 'visit_id' => $encounter->visit_id, 'encounter_id' => $encounter->id,
                'entry_type' => 'assessment', 'content_json' => ['text' => 'Diagnosis final'], 'author_id' => $doctor->id,
                'author_role' => 'doctor', 'clinical_time' => now(), 'recorded_at' => now()->addSeconds($index),
                'finalized_at' => now()->addSeconds($index), 'entry_status' => 'original', 'integrity_hash' => hash('sha256', 'entry-'.$index),
            ]);
            DiagnosisEntry::query()->create([
                'clinical_entry_id' => $entry->id, 'diagnosis_code' => $index < 2 ? 'J06.9' : 'K02.9',
                'diagnosis_name' => $index < 2 ? 'ISPA' : 'Karies gigi', 'diagnosis_type' => 'primary', 'is_primary' => true,
            ]);
        }

        $report = app(ClinicReport::class)->forPeriod(now()->startOfDay(), now()->addDay()->startOfDay());

        $this->assertSame(2, $report['visits']);
        $this->assertSame(3, $report['encounters']);
        $this->assertSame(1, $report['unique_patients']);
        $this->assertSame('J06.9', $report['top_diagnoses']->first()->diagnosis_code);
        $this->assertSame(3, (int) $report['provider_workload']->first()->encounter_count);
        $this->assertSame(1, (int) $report['provider_workload']->first()->unique_patient_count);
    }

    private function patient(User $staff, string $mrn, string $name, ?string $canonical = null, string $status = 'active'): Patient
    {
        return Patient::query()->create(['medical_record_number' => $mrn, 'full_name' => $name, 'birth_date' => '1988-04-12', 'sex' => 'female', 'canonical_patient_id' => $canonical, 'status' => $status, 'created_by' => $staff->id]);
    }

    private function visit(User $staff, Patient $patient, string $code): Visit
    {
        $registration = Registration::query()->create(['patient_id' => $patient->id, 'registration_date' => now()->toDateString(), 'channel' => 'front_desk', 'payer_type' => 'general', 'requested_service' => 'general', 'status' => 'completed', 'booking_code' => $code, 'created_by' => $staff->id]);

        return Visit::query()->create(['patient_id' => $patient->id, 'registration_id' => $registration->id, 'visit_date' => now()->toDateString(), 'payer_type' => 'general', 'status' => 'completed', 'arrived_at' => now()->subHour(), 'completed_at' => now()]);
    }

    private function encounter(User $doctor, Visit $visit, string $service): Encounter
    {
        return Encounter::query()->create(['visit_id' => $visit->id, 'service_type' => $service, 'responsible_provider_id' => $doctor->id, 'status' => 'finalized', 'started_at' => now()->subMinutes(30), 'finalized_at' => now()]);
    }
}

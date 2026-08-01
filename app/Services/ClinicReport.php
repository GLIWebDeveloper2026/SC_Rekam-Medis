<?php

namespace App\Services;

use App\Models\ClinicalEntry;
use App\Models\DiagnosisEntry;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ClinicReport
{
    /**
     * @return array{
     *     visits: int,
     *     encounters: int,
     *     unique_patients: int,
     *     new_patients: int,
     *     general_visits: int,
     *     insurance_visits: int,
     *     top_diagnoses: Collection<int, object>,
     *     provider_workload: Collection<int, object>
     * }
     */
    public function forPeriod(CarbonInterface $start, CarbonInterface $end): array
    {
        $visits = Visit::query()
            ->where('status', 'completed')
            ->where('visit_date', '>=', $start->toDateString())
            ->where('visit_date', '<', $end->toDateString());
        $encounters = Encounter::query()
            ->where('status', 'finalized')
            ->where('finalized_at', '>=', $start)
            ->where('finalized_at', '<', $end);

        $visitTable = (new Visit)->getTable();
        $patientTable = (new Patient)->getTable();
        $encounterTable = (new Encounter)->getTable();
        $userTable = (new User)->getTable();
        $clinicalEntryTable = (new ClinicalEntry)->getTable();
        $diagnosisTable = (new DiagnosisEntry)->getTable();

        $uniquePatients = Visit::query()
            ->join($patientTable, "{$patientTable}.id", '=', "{$visitTable}.patient_id")
            ->where("{$visitTable}.status", 'completed')
            ->where("{$visitTable}.visit_date", '>=', $start->toDateString())
            ->where("{$visitTable}.visit_date", '<', $end->toDateString())
            ->selectRaw("COUNT(DISTINCT COALESCE({$patientTable}.canonical_patient_id, {$patientTable}.id)) AS aggregate")
            ->value('aggregate');

        $topDiagnoses = DiagnosisEntry::query()
            ->join($clinicalEntryTable, "{$clinicalEntryTable}.id", '=', "{$diagnosisTable}.clinical_entry_id")
            ->where("{$clinicalEntryTable}.entry_status", '!=', 'void_notice')
            ->where("{$clinicalEntryTable}.finalized_at", '>=', $start)
            ->where("{$clinicalEntryTable}.finalized_at", '<', $end)
            ->selectRaw("{$diagnosisTable}.diagnosis_code, {$diagnosisTable}.diagnosis_name, COUNT(*) AS diagnosis_count")
            ->groupBy("{$diagnosisTable}.diagnosis_code", "{$diagnosisTable}.diagnosis_name")
            ->orderByDesc('diagnosis_count')
            ->limit(10)
            ->get();

        $providerWorkload = Encounter::query()
            ->join($visitTable, "{$visitTable}.id", '=', "{$encounterTable}.visit_id")
            ->join($patientTable, "{$patientTable}.id", '=', "{$visitTable}.patient_id")
            ->join($userTable, "{$userTable}.id", '=', "{$encounterTable}.responsible_provider_id")
            ->where("{$encounterTable}.status", 'finalized')
            ->where("{$encounterTable}.finalized_at", '>=', $start)
            ->where("{$encounterTable}.finalized_at", '<', $end)
            ->selectRaw("{$userTable}.id AS provider_id, {$userTable}.name AS provider_name, COUNT({$encounterTable}.id) AS encounter_count, COUNT(DISTINCT COALESCE({$patientTable}.canonical_patient_id, {$patientTable}.id)) AS unique_patient_count")
            ->groupBy("{$userTable}.id", "{$userTable}.name")
            ->orderByDesc('encounter_count')
            ->get();

        return [
            'visits' => (clone $visits)->count(),
            'encounters' => (clone $encounters)->count(),
            'unique_patients' => (int) $uniquePatients,
            'new_patients' => Patient::query()->where('created_at', '>=', $start)->where('created_at', '<', $end)->count(),
            'general_visits' => (clone $visits)->where('payer_type', 'general')->count(),
            'insurance_visits' => (clone $visits)->where('payer_type', 'insurance')->count(),
            'top_diagnoses' => $topDiagnoses,
            'provider_workload' => $providerWorkload,
        ];
    }
}

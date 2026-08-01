<?php

namespace App\Http\Controllers;

use App\Actions\Patients\MergePatients;
use App\Models\Patient;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientMergeController extends Controller
{
    public function store(Request $request, MergePatients $mergePatients, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'canonical_patient_id' => ['required', 'uuid', 'exists:patients,id', 'different:source_patient_id'],
            'source_patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
            'approved_by' => ['required', 'uuid', 'exists:users,id', 'different:performed_by'],
        ]);

        $canonical = Patient::query()->findOrFail($data['canonical_patient_id']);
        $source = Patient::query()->findOrFail($data['source_patient_id']);
        $case = $mergePatients->execute($canonical, $source, $data['reason'], $request->user()->id, $data['approved_by']);

        $auditTrail->record('patient.merged', 'patient_merge_case', $case->id, 'success', $request->user(), $canonical->id, $data['reason']);

        return redirect()->route('patients.show', $canonical)->with('status', 'Identitas pasien berhasil digabung secara logis.');
    }
}

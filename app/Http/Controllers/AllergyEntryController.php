<?php

namespace App\Http\Controllers;

use App\Models\AllergyEntry;
use App\Models\Patient;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AllergyEntryController extends Controller
{
    public function store(Request $request, Patient $patient, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'substance_name' => ['required', 'string', 'max:255'],
            'reaction' => ['nullable', 'string', 'max:2000'],
            'severity' => ['required', 'in:mild,moderate,severe,unknown'],
            'clinical_status' => ['required', 'in:active,inactive,refuted'],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = [
            ...$data,
            'patient_id' => $patient->id,
            'verification_status' => 'confirmed',
            'recorded_by' => $request->user()->id,
            'recorded_at' => now(),
        ];
        $entry = AllergyEntry::query()->create([
            ...$payload,
            'integrity_hash' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
        ]);

        $auditTrail->record('allergy.created', 'allergy_entry', $entry->id, 'success', $request->user(), $patient->id);

        return redirect()->route('patients.show', $patient)->with('status', 'Alergi pasien dicatat.');
    }
}

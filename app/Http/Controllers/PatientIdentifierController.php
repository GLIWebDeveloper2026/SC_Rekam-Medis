<?php

namespace App\Http\Controllers;

use App\Actions\Patients\AttachPatientIdentifier;
use App\Models\Patient;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientIdentifierController extends Controller
{
    public function store(
        Request $request,
        Patient $patient,
        AttachPatientIdentifier $attachIdentifier,
        AuditTrail $auditTrail,
    ): RedirectResponse {
        $data = $request->validate([
            'identifier_type' => ['required', 'in:nik,birth_certificate,insurer_number,passport,temporary'],
            'identifier_value' => ['required', 'string', 'max:255'],
            'verified_status' => ['required', 'in:unverified,verified,rejected'],
            'source' => ['nullable', 'string', 'max:255'],
            'valid_from' => ['nullable', 'date'],
        ]);

        if ($data['identifier_type'] === 'nik') {
            validator($data, ['identifier_value' => ['digits:16']])->validate();
        }

        $identifier = $attachIdentifier->execute($patient, $data, $request->user()->id);
        $auditTrail->record('patient.identifier_added', 'patient_identifier', $identifier->id, 'success', $request->user(), $patient->id);

        return redirect()->route('patients.show', $patient)->with('status', 'Identifier pasien ditambahkan.');
    }
}

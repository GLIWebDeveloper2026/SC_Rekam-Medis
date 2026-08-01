<?php

namespace App\Http\Controllers;

use App\Actions\Pharmacy\CorrectPrescription;
use App\Actions\Pharmacy\FinalizePrescription;
use App\Models\Encounter;
use App\Models\Prescription;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class PrescriptionController extends Controller
{
    public function store(Request $request, Encounter $encounter, FinalizePrescription $finalize, AuditTrail $auditTrail): RedirectResponse
    {
        $validator = validator($request->all(), [
            'medicine_id' => ['required', 'uuid', 'exists:medicines,id'], 'dosage' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'], 'route' => ['required', 'string', 'max:80'],
            'duration' => ['nullable', 'string', 'max:100'], 'quantity' => ['required', 'numeric', 'gt:0'],
            'instruction' => ['nullable', 'string', 'max:2000'], 'preparation_type' => ['required', 'in:finished,compound'],
            'allergy_acknowledged' => ['nullable', 'accepted'],
        ]);
        $validator->after(function (Validator $validator) use ($encounter, $request): void {
            $patient = $encounter->visit()->with('patient')->firstOrFail()->patient;
            if ($patient->hasActiveAllergy() && ! $request->boolean('allergy_acknowledged')) {
                $validator->errors()->add('allergy_acknowledged', 'Peringatan alergi wajib diakui sebelum finalisasi resep.');
            }
        });
        $data = $validator->validate();
        $prescription = $finalize->execute($encounter, $data, $request->user());
        $auditTrail->record('prescription.finalized', 'prescription', $prescription->id, 'success', $request->user(), $prescription->patient_id);

        return redirect()->route('pharmacy.index')->with('status', 'Resep final dikirim ke farmasi.');
    }

    public function correct(Request $request, Prescription $prescription, CorrectPrescription $correct, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'dosage' => ['required', 'string', 'max:255'], 'frequency' => ['required', 'string', 'max:255'],
            'route' => ['required', 'string', 'max:80'], 'duration' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'gt:0'], 'instruction' => ['nullable', 'string', 'max:2000'],
            'correction_reason' => ['required', 'string', 'min:10', 'max:2000'],
            'dispensing_impact' => ['required', 'in:not_prepared,preparing,already_dispensed'],
        ]);
        $correction = $correct->execute($prescription, $data, $request->user());
        $auditTrail->record('prescription.corrected', 'prescription', $correction->id, 'success', $request->user(), $correction->patient_id, $data['correction_reason']);

        return redirect()->route('pharmacy.index')->with('status', 'Resep koreksi dibuat; resep awal tetap tersimpan.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Documents\GenerateMedicalRecordCopy;
use App\Http\Requests\Documents\StoreMedicalRecordCopyRequest;
use App\Models\MedicalRecordCopyRequest;
use App\Models\Patient;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalRecordCopyController extends Controller
{
    public function index(): View
    {
        return view('record-copies.index', [
            'requests' => MedicalRecordCopyRequest::query()->with('patient')->latest()->paginate(20),
            'patients' => Patient::query()->where('status', 'active')->orderBy('full_name')->limit(100)->get(),
        ]);
    }

    public function store(StoreMedicalRecordCopyRequest $request, AuditTrail $auditTrail): RedirectResponse
    {
        $copyRequest = MedicalRecordCopyRequest::query()->create([
            ...$request->validated(),
            'status' => 'submitted',
            'created_by' => $request->user()->id,
        ]);
        $auditTrail->record('medical_record_copy.requested', 'medical_record_copy_request', $copyRequest->id, 'success', $request->user(), $copyRequest->patient_id, $copyRequest->purpose);

        return redirect()->route('record-copies.index')->with('status', 'Permintaan salinan rekam medis dicatat.');
    }

    public function approve(Request $request, MedicalRecordCopyRequest $medicalRecordCopyRequest, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate(['identity_verified' => ['accepted'], 'approval_notes' => ['required', 'string', 'min:10', 'max:2000']]);
        $medicalRecordCopyRequest->update([
            'status' => 'approved',
            'identity_verified_by' => $request->user()->id,
            'approved_by' => $request->user()->id,
            'approval_notes' => $data['approval_notes'],
            'approved_at' => now(),
        ]);
        $auditTrail->record('medical_record_copy.approved', 'medical_record_copy_request', $medicalRecordCopyRequest->id, 'success', $request->user(), $medicalRecordCopyRequest->patient_id, $data['approval_notes']);

        return redirect()->route('record-copies.index')->with('status', 'Permintaan telah diverifikasi dan disetujui.');
    }

    public function generate(
        Request $request,
        MedicalRecordCopyRequest $medicalRecordCopyRequest,
        GenerateMedicalRecordCopy $generate,
        AuditTrail $auditTrail,
    ): RedirectResponse {
        $document = $generate->execute($medicalRecordCopyRequest, $request->user());
        $auditTrail->record('medical_record_copy.generated', 'generated_document', $document->id, 'success', $request->user(), $document->patient_id);

        return redirect()->route('record-copies.index')->with('status', 'PDF salinan terkontrol berhasil dibuat.');
    }
}

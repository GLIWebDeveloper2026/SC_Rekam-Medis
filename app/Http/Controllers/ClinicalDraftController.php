<?php

namespace App\Http\Controllers;

use App\Actions\Clinical\FinalizeClinicalDraft;
use App\Models\ClinicalDraft;
use App\Models\Encounter;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClinicalDraftController extends Controller
{
    public function store(Request $request, Encounter $encounter, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'entry_type' => ['required', 'in:anamnesis,examination,assessment,plan,education,progress'],
            'content' => ['required', 'string', 'min:3', 'max:20000'],
        ]);

        if ($encounter->responsible_provider_id !== $request->user()->id) {
            abort(403);
        }

        $draft = ClinicalDraft::query()->updateOrCreate(
            [
                'encounter_id' => $encounter->id,
                'author_id' => $request->user()->id,
                'entry_type' => $data['entry_type'],
                'status' => 'active',
            ],
            ['content_json' => ['text' => $data['content']], 'expires_at' => now()->addDays(7)],
        );
        $auditTrail->record('clinical_draft.saved', 'clinical_draft', $draft->id, 'success', $request->user(), $encounter->visit()->value('patient_id'));

        return redirect()->route('clinical.workspace')->with('status', 'Draft klinis tersimpan.');
    }

    public function finalize(
        Request $request,
        ClinicalDraft $clinicalDraft,
        FinalizeClinicalDraft $finalize,
        AuditTrail $auditTrail,
    ): RedirectResponse {
        $data = $request->validate([
            'clinical_time' => ['required', 'date'],
            'diagnosis_code' => ['nullable', 'string', 'max:40', 'required_with:diagnosis_name'],
            'diagnosis_name' => ['nullable', 'string', 'max:255', 'required_with:diagnosis_code'],
            'diagnosis_type' => ['nullable', 'in:primary,secondary,differential'],
            'is_primary' => ['nullable', 'boolean'],
            'confirmation' => ['accepted'],
        ]);

        $entry = $finalize->execute($clinicalDraft, $data, $request->user());
        $auditTrail->record('clinical_entry.finalized', 'clinical_entry', $entry->id, 'success', $request->user(), $entry->patient_id);

        return redirect()->route('clinical-entries.show', $entry)->with('status', 'Catatan klinis difinalisasi dan tidak dapat diubah.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Clinical\CreateClinicalAddendum;
use App\Models\ClinicalEntry;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalEntryController extends Controller
{
    public function show(Request $request, ClinicalEntry $clinicalEntry, AuditTrail $auditTrail): View
    {
        $timeline = ClinicalEntry::query()
            ->with(['diagnoses', 'supersedes'])
            ->where('encounter_id', $clinicalEntry->encounter_id)
            ->orderBy('recorded_at')
            ->get();
        $auditTrail->record('clinical_entry.view', 'clinical_entry', $clinicalEntry->id, 'success', $request->user(), $clinicalEntry->patient_id);

        return view('clinical.show', compact('clinicalEntry', 'timeline'));
    }

    public function addendum(
        Request $request,
        ClinicalEntry $clinicalEntry,
        CreateClinicalAddendum $createAddendum,
        AuditTrail $auditTrail,
    ): RedirectResponse {
        $data = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:20000'],
            'correction_reason' => ['required', 'string', 'min:10', 'max:2000'],
            'clinical_time' => ['required', 'date'],
            'confirmation' => ['accepted'],
        ]);
        $entry = $createAddendum->execute($clinicalEntry, $data, $request->user());
        $auditTrail->record('clinical_entry.addendum_created', 'clinical_entry', $entry->id, 'success', $request->user(), $entry->patient_id, $data['correction_reason']);

        return redirect()->route('clinical-entries.show', $entry)->with('status', 'Addendum berhasil ditambahkan tanpa mengubah catatan awal.');
    }
}

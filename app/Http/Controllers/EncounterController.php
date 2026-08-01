<?php

namespace App\Http\Controllers;

use App\Models\Encounter;
use App\Models\Visit;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncounterController extends Controller
{
    public function store(Request $request, Visit $visit, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'service_type' => ['required', 'in:general,dental,nursing'],
            'responsible_provider_id' => ['required', 'uuid', 'exists:users,id'],
            'referral_from_encounter_id' => ['nullable', 'uuid', 'exists:encounters,id'],
        ]);

        $encounter = DB::transaction(fn () => Encounter::query()->create([
            'visit_id' => $visit->id,
            ...$data,
            'status' => 'active',
            'started_at' => now(),
        ]), attempts: 5);

        $auditTrail->record('encounter.created', 'encounter', $encounter->id, 'success', $request->user(), $visit->patient_id);

        return redirect()->route('clinical.workspace')->with('status', 'Encounter berhasil dibuat.');
    }
}

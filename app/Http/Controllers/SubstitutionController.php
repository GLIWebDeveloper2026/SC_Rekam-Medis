<?php

namespace App\Http\Controllers;

use App\Models\PrescriptionItem;
use App\Models\SubstitutionRequest;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubstitutionController extends Controller
{
    public function store(Request $request, PrescriptionItem $prescriptionItem, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate(['proposed_medicine_id' => ['required', 'uuid', 'exists:medicines,id'], 'reason' => ['required', 'string', 'min:20', 'max:2000']]);
        $substitution = SubstitutionRequest::query()->create([
            'prescription_item_id' => $prescriptionItem->id, 'proposed_medicine_id' => $data['proposed_medicine_id'],
            'reason' => $data['reason'], 'proposed_by' => $request->user()->id, 'status' => 'waiting_doctor', 'created_at' => now(),
        ]);
        $this->event($substitution, 'proposed', null, $request->user()->id, null, null, $data['reason']);
        $patientId = $prescriptionItem->prescription()->value('patient_id');
        $auditTrail->record('substitution.proposed', 'substitution_request', $substitution->id, 'success', $request->user(), $patientId, $data['reason']);

        return redirect()->route('pharmacy.index')->with('status', 'Usulan substitusi dikirim ke dokter.');
    }

    public function verbalApproval(Request $request, SubstitutionRequest $substitutionRequest, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'uuid', 'exists:users,id'], 'communication_channel' => ['required', 'string', 'max:50'],
            'verbal_approval_at' => ['required', 'date'], 'notes' => ['required', 'string', 'min:20', 'max:2000'],
        ]);
        $substitutionRequest->update(['status' => 'verbal_approved_pending_ratification']);
        $this->event($substitutionRequest, 'verbal_approved', $data['doctor_id'], $request->user()->id, $data['communication_channel'], $data['verbal_approval_at'], $data['notes']);
        $auditTrail->record('substitution.verbal_approved', 'substitution_request', $substitutionRequest->id, 'success', $request->user(), reason: $data['notes']);

        return redirect()->route('pharmacy.index')->with('status', 'Persetujuan verbal dicatat dan menunggu ratifikasi.');
    }

    public function ratify(Request $request, SubstitutionRequest $substitutionRequest, AuditTrail $auditTrail): RedirectResponse
    {
        $request->validate(['confirmation' => ['accepted']]);
        $verbal = DB::table('substitution_events')->where('substitution_request_id', $substitutionRequest->id)->where('event_type', 'verbal_approved')->latest('created_at')->first();
        abort_unless($verbal !== null && $verbal->doctor_id === $request->user()->id, 403);
        $substitutionRequest->update(['status' => 'ratified']);
        $this->event($substitutionRequest, 'ratified', $request->user()->id, $request->user()->id, null, null, 'Ratifikasi digital oleh dokter.');
        $auditTrail->record('substitution.ratified', 'substitution_request', $substitutionRequest->id, 'success', $request->user());

        return redirect()->route('pharmacy.index')->with('status', 'Substitusi telah diratifikasi secara digital.');
    }

    private function event(SubstitutionRequest $request, string $type, ?string $doctorId, string $recordedBy, ?string $channel, mixed $verbalAt, string $notes): void
    {
        $createdAt = now();
        $payload = ['substitution_request_id' => $request->id, 'event_type' => $type, 'doctor_id' => $doctorId, 'recorded_by' => $recordedBy, 'communication_channel' => $channel, 'verbal_approval_at' => $verbalAt, 'ratified_at' => $type === 'ratified' ? $createdAt->format('Y-m-d H:i:s.u') : null, 'notes' => $notes, 'created_at' => $createdAt->format('Y-m-d H:i:s.u')];
        DB::table('substitution_events')->insert(['id' => (string) Str::uuid(), ...$payload, 'created_at' => $createdAt, 'integrity_hash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR))]);
    }
}

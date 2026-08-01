<?php

namespace App\Http\Controllers;

use App\Actions\Queue\RecordTriage;
use App\Models\QueueTicket;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TriageController extends Controller
{
    public function store(Request $request, QueueTicket $queueTicket, RecordTriage $recordTriage, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'chief_complaint' => ['required', 'string', 'max:2000'],
            'priority_level' => ['required', 'in:routine,urgent,emergency'],
            'priority_reason' => ['required', 'string', 'min:10', 'max:2000'],
            'temperature' => ['nullable', 'numeric', 'between:30,45'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'between:40,300'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'between:20,200'],
            'pulse' => ['nullable', 'integer', 'between:20,250'],
            'respiratory_rate' => ['nullable', 'integer', 'between:5,80'],
            'weight' => ['nullable', 'numeric', 'between:0.2,500'],
            'height' => ['nullable', 'numeric', 'between:20,250'],
        ]);

        $recordTriage->execute($queueTicket, $data, $request->user()->id);
        $patientId = $queueTicket->registration()->value('patient_id');
        $auditTrail->record('queue.triaged', 'queue_ticket', $queueTicket->id, 'success', $request->user(), $patientId, $data['priority_reason']);

        return redirect()->route('queue.index')->with('status', 'Triage dan prioritas berhasil dicatat.');
    }
}

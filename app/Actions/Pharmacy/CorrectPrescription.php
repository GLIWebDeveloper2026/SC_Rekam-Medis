<?php

namespace App\Actions\Pharmacy;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CorrectPrescription
{
    public function __construct(private readonly FinalizePrescription $events) {}

    /** @param array<string, mixed> $data */
    public function execute(Prescription $original, array $data, User $doctor): Prescription
    {
        return DB::transaction(function () use ($original, $data, $doctor): Prescription {
            $originalItem = $original->items()->firstOrFail();
            $finalizedAt = now();
            $previousHash = Prescription::query()->where('patient_id', $original->patient_id)->lockForUpdate()->latest('finalized_at')->value('integrity_hash');
            $payload = [
                'patient_id' => $original->patient_id, 'visit_id' => $original->visit_id, 'encounter_id' => $original->encounter_id,
                'prescriber_id' => $doctor->id, 'status' => 'finalized', 'finalized_at' => $finalizedAt->format('Y-m-d H:i:s.u'),
                'corrects_prescription_id' => $original->id, 'previous_hash' => $previousHash,
            ];
            $correction = Prescription::query()->create([...$payload, 'integrity_hash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR))]);
            $itemPayload = [
                'prescription_id' => $correction->id, 'medicine_id' => $originalItem->medicine_id,
                'medicine_name_snapshot' => $originalItem->medicine_name_snapshot, 'strength_snapshot' => $originalItem->strength_snapshot,
                'dosage' => $data['dosage'], 'frequency' => $data['frequency'], 'route' => $data['route'],
                'duration' => $data['duration'] ?? null, 'quantity' => $data['quantity'], 'instruction' => $data['instruction'] ?? null,
                'preparation_type' => $originalItem->preparation_type,
            ];
            PrescriptionItem::query()->create([...$itemPayload, 'integrity_hash' => hash('sha256', json_encode($itemPayload, JSON_THROW_ON_ERROR))]);
            $this->events->event($original, 'corrected', $doctor->id, $data['correction_reason'].' Dampak: '.$data['dispensing_impact']);
            $this->events->event($correction, 'correction_finalized', $doctor->id, $data['correction_reason']);

            return $correction;
        }, attempts: 5);
    }
}

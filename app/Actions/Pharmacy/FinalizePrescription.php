<?php

namespace App\Actions\Pharmacy;

use App\Models\Encounter;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinalizePrescription
{
    /** @param array<string, mixed> $data */
    public function execute(Encounter $encounter, array $data, User $doctor): Prescription
    {
        return DB::transaction(function () use ($encounter, $data, $doctor): Prescription {
            $encounter->load('visit');
            $medicine = Medicine::query()->findOrFail($data['medicine_id']);
            $finalizedAt = now();
            $previousHash = Prescription::query()->where('patient_id', $encounter->visit->patient_id)->lockForUpdate()->latest('finalized_at')->value('integrity_hash');
            $payload = [
                'patient_id' => $encounter->visit->patient_id, 'visit_id' => $encounter->visit_id,
                'encounter_id' => $encounter->id, 'prescriber_id' => $doctor->id, 'status' => 'finalized',
                'finalized_at' => $finalizedAt->format('Y-m-d H:i:s.u'), 'previous_hash' => $previousHash,
            ];
            $prescription = Prescription::query()->create([...$payload, 'integrity_hash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR))]);
            $itemPayload = [
                'prescription_id' => $prescription->id, 'medicine_id' => $medicine->id,
                'medicine_name_snapshot' => $medicine->generic_name, 'strength_snapshot' => $medicine->strength,
                'dosage' => $data['dosage'], 'frequency' => $data['frequency'], 'route' => $data['route'],
                'duration' => $data['duration'] ?? null, 'quantity' => $data['quantity'],
                'instruction' => $data['instruction'] ?? null, 'preparation_type' => $data['preparation_type'],
            ];
            PrescriptionItem::query()->create([...$itemPayload, 'integrity_hash' => hash('sha256', json_encode($itemPayload, JSON_THROW_ON_ERROR))]);
            $this->event($prescription, 'finalized', $doctor->id, 'Resep difinalisasi dan dikirim ke farmasi.');

            return $prescription;
        }, attempts: 5);
    }

    public function event(Prescription $prescription, string $type, string $userId, ?string $notes): void
    {
        $previous = DB::table('prescription_events')->where('prescription_id', $prescription->id)->latest('created_at')->value('integrity_hash');
        $createdAt = now();
        $payload = ['prescription_id' => $prescription->id, 'event_type' => $type, 'performed_by' => $userId, 'notes' => $notes, 'created_at' => $createdAt->format('Y-m-d H:i:s.u'), 'previous_hash' => $previous];
        DB::table('prescription_events')->insert(['id' => (string) Str::uuid(), ...$payload, 'created_at' => $createdAt, 'integrity_hash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR))]);
    }
}

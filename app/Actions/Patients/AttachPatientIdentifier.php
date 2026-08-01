<?php

namespace App\Actions\Patients;

use App\Models\Patient;
use App\Models\PatientIdentifier;
use Illuminate\Validation\ValidationException;

class AttachPatientIdentifier
{
    /** @param array<string, mixed> $data */
    public function execute(Patient $patient, array $data, string $userId): PatientIdentifier
    {
        $normalized = preg_replace('/\s+/', '', strtolower((string) $data['identifier_value'])) ?? '';
        $hash = hash('sha256', $normalized);

        if (PatientIdentifier::query()
            ->where('identifier_type', $data['identifier_type'])
            ->where('normalized_hash', $hash)
            ->where('patient_id', '!=', $patient->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'identifier_value' => 'Identifier aktif tersebut sudah digunakan pasien lain.',
            ]);
        }

        return PatientIdentifier::query()->create([
            'patient_id' => $patient->id,
            'identifier_type' => $data['identifier_type'],
            'identifier_value' => $data['identifier_value'],
            'normalized_hash' => $hash,
            'verified_status' => $data['verified_status'],
            'source' => $data['source'] ?? 'registration',
            'valid_from' => $data['valid_from'] ?? now()->toDateString(),
            'recorded_by' => $userId,
            'recorded_at' => now(),
        ]);
    }
}

<?php

namespace App\Actions\Patients;

use App\Models\Patient;
use App\Models\PatientIdentifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterPatient
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, string $userId): Patient
    {
        return DB::transaction(function () use ($data, $userId): Patient {
            $period = now()->format('Ym');
            $counterKey = 'mrn:'.$period;

            DB::table('business_counters')->insertOrIgnore([
                'key' => $counterKey,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $counter = DB::table('business_counters')->where('key', $counterKey)->lockForUpdate()->first();
            $nextNumber = ((int) $counter->last_number) + 1;
            DB::table('business_counters')->where('key', $counterKey)->update([
                'last_number' => $nextNumber,
                'updated_at' => now(),
            ]);

            $patient = Patient::query()->create([
                'medical_record_number' => sprintf('RM-%s-%06d', $period, $nextNumber),
                'full_name' => $data['full_name'],
                'normalized_name' => $this->normalizeName($data['full_name']),
                'birth_date' => $data['birth_date'],
                'sex' => $data['sex'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => 'active',
                'created_by' => $userId,
            ]);

            if (! empty($data['guardian_name'])) {
                $patient->guardians()->create([
                    'guardian_name' => $data['guardian_name'],
                    'relationship' => $data['guardian_relationship'],
                    'phone' => $data['guardian_phone'] ?? null,
                    'valid_from' => now()->toDateString(),
                ]);
            }

            $identifierType = empty($data['nik']) ? 'temporary' : 'nik';
            $identifierValue = empty($data['nik']) ? 'TEMP-'.Str::upper(Str::random(12)) : $data['nik'];
            PatientIdentifier::query()->create([
                'patient_id' => $patient->id,
                'identifier_type' => $identifierType,
                'identifier_value' => $identifierValue,
                'normalized_hash' => hash('sha256', $this->normalizeIdentifier($identifierValue)),
                'verified_status' => $identifierType === 'nik' ? 'unverified' : 'verified',
                'source' => 'registration',
                'recorded_by' => $userId,
                'recorded_at' => now(),
            ]);

            return $patient;
        }, attempts: 5);
    }

    private function normalizeName(string $name): string
    {
        return Str::of($name)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->toString();
    }

    private function normalizeIdentifier(string $value): string
    {
        return preg_replace('/\s+/', '', Str::lower($value)) ?? '';
    }
}

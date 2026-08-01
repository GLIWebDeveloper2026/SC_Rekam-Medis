<?php

namespace App\Actions\Clinical;

use App\Models\ClinicalDraft;
use App\Models\ClinicalEntry;
use App\Models\DiagnosisEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinalizeClinicalDraft
{
    /** @param array<string, mixed> $data */
    public function execute(ClinicalDraft $draft, array $data, User $author): ClinicalEntry
    {
        if ($draft->author_id !== $author->id || $draft->status !== 'active') {
            throw ValidationException::withMessages(['draft' => 'Draft hanya dapat difinalisasi satu kali oleh penulisnya.']);
        }

        return DB::transaction(function () use ($draft, $data, $author): ClinicalEntry {
            $draft = ClinicalDraft::query()->whereKey($draft)->lockForUpdate()->firstOrFail();
            $encounter = $draft->encounter()->with('visit')->lockForUpdate()->firstOrFail();
            $recordedAt = now();
            $previousHash = ClinicalEntry::query()
                ->where('patient_id', $encounter->visit->patient_id)
                ->lockForUpdate()
                ->latest('recorded_at')
                ->value('integrity_hash');
            $payload = [
                'patient_id' => $encounter->visit->patient_id,
                'visit_id' => $encounter->visit_id,
                'encounter_id' => $encounter->id,
                'entry_type' => $draft->entry_type,
                'content_json' => $draft->content_json,
                'author_id' => $author->id,
                'author_role' => $author->activeRoleCode() ?? 'clinical',
                'clinical_time' => $data['clinical_time'],
                'recorded_at' => $recordedAt->format('Y-m-d H:i:s.u'),
                'finalized_at' => $recordedAt->format('Y-m-d H:i:s.u'),
                'entry_status' => 'original',
                'previous_hash' => $previousHash,
            ];
            $entry = ClinicalEntry::query()->create([
                ...$payload,
                'integrity_hash' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
            ]);

            if (! empty($data['diagnosis_code']) && ! empty($data['diagnosis_name'])) {
                DiagnosisEntry::query()->create([
                    'clinical_entry_id' => $entry->id,
                    'diagnosis_code' => $data['diagnosis_code'],
                    'diagnosis_name' => $data['diagnosis_name'],
                    'diagnosis_type' => $data['diagnosis_type'] ?? 'primary',
                    'is_primary' => (bool) ($data['is_primary'] ?? true),
                ]);
            }

            $draft->update(['status' => 'finalized', 'expires_at' => $recordedAt]);
            $encounter->update(['status' => 'finalized', 'finalized_at' => $recordedAt]);

            return $entry;
        }, attempts: 5);
    }
}

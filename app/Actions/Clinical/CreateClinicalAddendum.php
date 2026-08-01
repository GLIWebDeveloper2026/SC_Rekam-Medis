<?php

namespace App\Actions\Clinical;

use App\Models\ClinicalEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateClinicalAddendum
{
    /** @param array<string, mixed> $data */
    public function execute(ClinicalEntry $original, array $data, User $author): ClinicalEntry
    {
        return DB::transaction(function () use ($original, $data, $author): ClinicalEntry {
            $recordedAt = now();
            $previousHash = ClinicalEntry::query()
                ->where('patient_id', $original->patient_id)
                ->lockForUpdate()
                ->latest('recorded_at')
                ->value('integrity_hash');
            $payload = [
                'patient_id' => $original->patient_id,
                'visit_id' => $original->visit_id,
                'encounter_id' => $original->encounter_id,
                'entry_type' => 'addendum',
                'content_json' => ['text' => $data['content']],
                'author_id' => $author->id,
                'author_role' => $author->activeRoleCode() ?? 'clinical',
                'clinical_time' => $data['clinical_time'],
                'recorded_at' => $recordedAt->format('Y-m-d H:i:s.u'),
                'finalized_at' => $recordedAt->format('Y-m-d H:i:s.u'),
                'supersedes_entry_id' => $original->id,
                'correction_reason' => $data['correction_reason'],
                'entry_status' => 'addendum',
                'previous_hash' => $previousHash,
            ];

            return ClinicalEntry::query()->create([
                ...$payload,
                'integrity_hash' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
            ]);
        }, attempts: 5);
    }
}

<?php

namespace App\Actions\Patients;

use App\Models\Patient;
use App\Models\PatientMergeCase;
use App\Models\PatientMergeEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MergePatients
{
    public function execute(
        Patient $canonical,
        Patient $source,
        string $reason,
        string $performedBy,
        string $approvedBy,
    ): PatientMergeCase {
        if ($canonical->is($source) || $performedBy === $approvedBy) {
            throw ValidationException::withMessages([
                'source_patient_id' => 'Merge membutuhkan dua pasien berbeda serta pelaksana dan penyetuju berbeda.',
            ]);
        }

        return DB::transaction(function () use ($canonical, $source, $reason, $performedBy, $approvedBy): PatientMergeCase {
            Patient::query()->whereKey([$canonical->id, $source->id])->lockForUpdate()->get();

            $case = PatientMergeCase::query()->create([
                'status' => 'approved',
                'candidate_patient_a_id' => $canonical->id,
                'candidate_patient_b_id' => $source->id,
                'reason' => $reason,
                'created_by' => $performedBy,
                'reviewed_by' => $approvedBy,
                'decided_at' => now(),
            ]);

            $source->forceFill([
                'canonical_patient_id' => $canonical->resolvedCanonicalId(),
                'status' => 'merged',
            ])->save();

            PatientMergeEvent::query()->create([
                'merge_case_id' => $case->id,
                'canonical_patient_id' => $canonical->resolvedCanonicalId(),
                'source_patient_id' => $source->id,
                'event_type' => 'merged',
                'reason' => $reason,
                'performed_by' => $performedBy,
                'approved_by' => $approvedBy,
                'created_at' => now(),
            ]);

            return $case;
        }, attempts: 5);
    }
}

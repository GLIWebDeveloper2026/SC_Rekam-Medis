<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PatientMergeCase extends Model
{
    use HasUuids;

    protected $fillable = [
        'status', 'candidate_patient_a_id', 'candidate_patient_b_id', 'reason',
        'evidence_json', 'created_by', 'reviewed_by', 'decided_at',
    ];

    protected function casts(): array
    {
        return ['evidence_json' => 'array', 'decided_at' => 'datetime'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientIdentifier extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'patient_id', 'identifier_type', 'identifier_value', 'normalized_hash', 'verified_status',
        'source', 'valid_from', 'valid_until', 'recorded_by', 'recorded_at',
    ];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    protected function casts(): array
    {
        return [
            'identifier_value' => 'encrypted',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'recorded_at' => 'datetime',
        ];
    }
}

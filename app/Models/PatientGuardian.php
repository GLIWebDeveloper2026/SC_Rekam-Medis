<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientGuardian extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id', 'guardian_patient_id', 'guardian_name', 'relationship', 'phone', 'valid_from', 'valid_until',
    ];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

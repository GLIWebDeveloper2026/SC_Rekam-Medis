<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllergyEntry extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'patient_id', 'substance_code', 'substance_name', 'reaction', 'severity', 'clinical_status',
        'verification_status', 'source', 'recorded_by', 'recorded_at', 'supersedes_allergy_entry_id', 'integrity_hash',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Allergy entry bersifat append-only.'));
        static::deleting(fn () => throw new \LogicException('Allergy entry bersifat append-only.'));
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

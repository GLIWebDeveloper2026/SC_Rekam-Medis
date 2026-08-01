<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasUuids;

    protected $fillable = [
        'medical_record_number', 'canonical_patient_id', 'full_name', 'normalized_name',
        'birth_date', 'sex', 'phone', 'address', 'deceased_at', 'status', 'created_by',
    ];

    /** @return BelongsTo<Patient, $this> */
    public function canonicalPatient(): BelongsTo
    {
        return $this->belongsTo(self::class, 'canonical_patient_id');
    }

    /** @return HasMany<PatientIdentifier, $this> */
    public function identifiers(): HasMany
    {
        return $this->hasMany(PatientIdentifier::class);
    }

    /** @return HasMany<PatientGuardian, $this> */
    public function guardians(): HasMany
    {
        return $this->hasMany(PatientGuardian::class);
    }

    /** @return HasMany<AllergyEntry, $this> */
    public function allergyEntries(): HasMany
    {
        return $this->hasMany(AllergyEntry::class);
    }

    public function hasActiveAllergy(): bool
    {
        return $this->allergyEntries()->where('clinical_status', 'active')->exists();
    }

    public function resolvedCanonicalId(): string
    {
        return $this->canonical_patient_id ?? $this->id;
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'deceased_at' => 'datetime',
        ];
    }
}

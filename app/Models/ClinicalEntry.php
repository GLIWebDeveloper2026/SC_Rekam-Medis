<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicalEntry extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'patient_id', 'visit_id', 'encounter_id', 'entry_type', 'content_json', 'author_id',
        'author_role', 'clinical_time', 'recorded_at', 'finalized_at', 'supersedes_entry_id',
        'correction_reason', 'entry_status', 'integrity_hash', 'previous_hash',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Clinical entry final bersifat immutable.'));
        static::deleting(fn () => throw new \LogicException('Clinical entry final bersifat immutable.'));
    }

    /** @return BelongsTo<Encounter, $this> */
    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    /** @return HasMany<DiagnosisEntry, $this> */
    public function diagnoses(): HasMany
    {
        return $this->hasMany(DiagnosisEntry::class);
    }

    /** @return BelongsTo<ClinicalEntry, $this> */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_entry_id');
    }

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'clinical_time' => 'datetime',
            'recorded_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }
}

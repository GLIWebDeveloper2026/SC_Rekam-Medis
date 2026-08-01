<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['patient_id', 'visit_id', 'encounter_id', 'prescriber_id', 'status', 'finalized_at', 'corrects_prescription_id', 'cancellation_reason', 'integrity_hash', 'previous_hash'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Resep final bersifat immutable.'));
        static::deleting(fn () => throw new \LogicException('Resep final bersifat immutable.'));
    }

    /** @return HasMany<PrescriptionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    protected function casts(): array
    {
        return ['finalized_at' => 'datetime'];
    }
}

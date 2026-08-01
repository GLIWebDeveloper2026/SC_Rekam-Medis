<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['prescription_id', 'medicine_id', 'medicine_name_snapshot', 'strength_snapshot', 'dosage', 'frequency', 'route', 'duration', 'quantity', 'instruction', 'preparation_type', 'integrity_hash'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Item resep final bersifat immutable.'));
        static::deleting(fn () => throw new \LogicException('Item resep final bersifat immutable.'));
    }

    /** @return BelongsTo<Prescription, $this> */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}

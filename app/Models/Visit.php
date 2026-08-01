<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id', 'registration_id', 'visit_date', 'payer_type', 'status', 'arrived_at', 'completed_at',
    ];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /** @return HasMany<Encounter, $this> */
    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'arrived_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}

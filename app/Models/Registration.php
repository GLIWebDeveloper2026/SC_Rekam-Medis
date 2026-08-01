<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id', 'provider_schedule_id', 'registration_date', 'channel', 'payer_type',
        'coverage_id', 'requested_service', 'status', 'booking_code', 'created_by',
    ];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return HasOne<QueueTicket, $this> */
    public function queueTicket(): HasOne
    {
        return $this->hasOne(QueueTicket::class);
    }

    /** @return HasOne<Visit, $this> */
    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }
}

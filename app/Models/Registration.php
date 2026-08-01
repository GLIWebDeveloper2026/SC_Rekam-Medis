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

    /** @return BelongsTo<ProviderSchedule, $this> */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ProviderSchedule::class, 'provider_schedule_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    /** @return HasOne<Appointment, $this> */
    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    protected function casts(): array
    {
        return ['registration_date' => 'date'];
    }
}

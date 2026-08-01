<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, HasUuids;

    public const string StatusBooked = 'booked';

    public const string StatusCheckedIn = 'checked_in';

    public const string StatusCompleted = 'completed';

    public const string StatusCancelled = 'cancelled';

    protected $fillable = [
        'registration_id',
        'provider_schedule_id',
        'appointment_date',
        'slot_start',
        'slot_end',
        'status',
        'rescheduled_from_id',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'booked_at',
    ];

    protected $attributes = ['status' => self::StatusBooked];

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /** @return BelongsTo<ProviderSchedule, $this> */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ProviderSchedule::class, 'provider_schedule_id');
    }

    /** @return BelongsTo<Appointment, $this> */
    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'rescheduled_from_id');
    }

    /** @return BelongsTo<User, $this> */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /** @return HasMany<AppointmentEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(AppointmentEvent::class)->oldest('created_at');
    }

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'booked_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}

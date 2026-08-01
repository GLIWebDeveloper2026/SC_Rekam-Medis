<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'provider_user_id', 'service_type', 'day_of_week', 'start_time', 'end_time',
        'effective_from', 'effective_until', 'status',
    ];

    /** @return BelongsTo<User, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function isAvailableOn(CarbonInterface $date): bool
    {
        return $this->status === 'active'
            && $this->day_of_week === $date->isoWeekday()
            && $this->effective_from->lte($date)
            && ($this->effective_until === null || $this->effective_until->gte($date));
    }

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }
}

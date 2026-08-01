<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleException extends Model
{
    use HasUuids;

    protected $fillable = [
        'provider_schedule_id',
        'exception_date',
        'exception_type',
        'replacement_start',
        'replacement_end',
        'reason',
        'created_by',
    ];

    /** @return BelongsTo<ProviderSchedule, $this> */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ProviderSchedule::class, 'provider_schedule_id');
    }

    protected function casts(): array
    {
        return ['exception_date' => 'date'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueTicket extends Model
{
    use HasUuids;

    protected $fillable = [
        'registration_id', 'service_date', 'service_type', 'queue_number', 'original_position',
        'current_priority', 'status', 'checked_in_at',
    ];

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    protected function casts(): array
    {
        return ['service_date' => 'date', 'checked_in_at' => 'datetime'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Encounter extends Model
{
    use HasUuids;

    protected $fillable = [
        'visit_id', 'service_type', 'responsible_provider_id', 'referral_from_encounter_id',
        'status', 'started_at', 'finalized_at',
    ];

    /** @return BelongsTo<Visit, $this> */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function responsibleProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_provider_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalDraft extends Model
{
    use HasUuids;

    protected $fillable = ['encounter_id', 'author_id', 'entry_type', 'content_json', 'expires_at', 'status'];

    /** @return BelongsTo<Encounter, $this> */
    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    protected function casts(): array
    {
        return ['content_json' => 'array', 'expires_at' => 'datetime'];
    }
}

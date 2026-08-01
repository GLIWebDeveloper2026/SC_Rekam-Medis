<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubstitutionRequest extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['prescription_item_id', 'proposed_medicine_id', 'reason', 'proposed_by', 'status', 'created_at'];

    /** @return BelongsTo<PrescriptionItem, $this> */
    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
    }
}

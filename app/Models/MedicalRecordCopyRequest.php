<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordCopyRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id', 'requester_name', 'requester_relationship', 'purpose', 'requested_period_start',
        'requested_period_end', 'requested_scope', 'status', 'identity_verified_by', 'approved_by',
        'created_by', 'approval_notes', 'approved_at', 'released_at',
    ];

    protected $attributes = ['status' => 'submitted'];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    protected function casts(): array
    {
        return [
            'requested_period_start' => 'date',
            'requested_period_end' => 'date',
            'approved_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }
}

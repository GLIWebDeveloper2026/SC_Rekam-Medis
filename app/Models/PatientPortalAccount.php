<?php

namespace App\Models;

use Database\Factories\PatientPortalAccountFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientPortalAccount extends Model
{
    /** @use HasFactory<PatientPortalAccountFactory> */
    use HasFactory, HasUuids;

    public const string StatusPending = 'pending';

    public const string StatusApproved = 'approved';

    public const string StatusRejected = 'rejected';

    public const string StatusSuspended = 'suspended';

    protected $fillable = [
        'user_id',
        'patient_id',
        'status',
        'claimed_birth_date',
        'claimed_phone',
        'claimed_medical_record_number',
        'claimed_identifier_hash',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $attributes = ['status' => self::StatusPending];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::StatusApproved && $this->patient_id !== null;
    }

    protected function casts(): array
    {
        return [
            'claimed_birth_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }
}

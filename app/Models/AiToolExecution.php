<?php

namespace App\Models;

use Database\Factories\AiToolExecutionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiToolExecution extends Model
{
    /** @use HasFactory<AiToolExecutionFactory> */
    use HasFactory, HasUuids;

    public const string StatusPending = 'pending';

    public const string StatusSucceeded = 'succeeded';

    public const string StatusFailed = 'failed';

    protected $fillable = [
        'idempotency_key',
        'user_id',
        'patient_id',
        'active_role',
        'tool_name',
        'request_fingerprint',
        'status',
        'resource_type',
        'resource_id',
        'safe_input_json',
        'safe_output_json',
        'failure_code',
        'failure_summary',
        'started_at',
        'completed_at',
        'expires_at',
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

    protected function casts(): array
    {
        return [
            'safe_input_json' => 'array',
            'safe_output_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}

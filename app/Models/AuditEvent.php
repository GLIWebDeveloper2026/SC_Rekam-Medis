<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuditEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'occurred_at',
        'user_id',
        'active_role',
        'action',
        'resource_type',
        'resource_id',
        'patient_id',
        'result',
        'reason',
        'ip_address',
        'user_agent',
        'session_id',
        'metadata_json',
        'previous_hash',
        'integrity_hash',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Audit event bersifat immutable.'));
        static::deleting(fn () => throw new \LogicException('Audit event bersifat immutable.'));
    }

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }
}

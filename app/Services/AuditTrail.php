<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuditTrail
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        string $resourceType,
        ?string $resourceId,
        string $result,
        ?User $user = null,
        ?string $patientId = null,
        ?string $reason = null,
        array $metadata = [],
    ): AuditEvent {
        return DB::transaction(function () use (
            $action,
            $resourceType,
            $resourceId,
            $result,
            $user,
            $patientId,
            $reason,
            $metadata,
        ): AuditEvent {
            $previousHash = AuditEvent::query()
                ->lockForUpdate()
                ->latest('occurred_at')
                ->latest('id')
                ->value('integrity_hash');

            $occurredAt = now();
            $payload = [
                'occurred_at' => $occurredAt->format('Y-m-d H:i:s.u'),
                'user_id' => $user?->id,
                'active_role' => $user?->activeRoleCode(),
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'patient_id' => $patientId,
                'result' => $result,
                'reason' => $reason,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'session_id' => request()?->hasSession() ? request()->session()->getId() : null,
                'metadata_json' => $metadata,
                'previous_hash' => $previousHash,
            ];

            return AuditEvent::query()->create([
                ...$payload,
                'integrity_hash' => hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
            ]);
        }, attempts: 5);
    }
}

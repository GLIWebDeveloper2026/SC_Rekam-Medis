<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditTrail;
use Illuminate\Auth\Events\Logout;

class RecordLogout
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->auditTrail->record(
            action: 'session.logout',
            resourceType: 'user',
            resourceId: $event->user->id,
            result: 'success',
            user: $event->user,
        );
    }
}

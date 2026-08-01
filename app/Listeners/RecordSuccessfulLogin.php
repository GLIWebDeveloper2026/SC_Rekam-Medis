<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditTrail;
use Illuminate\Auth\Events\Login;

class RecordSuccessfulLogin
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $event->user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ])->save();

        $this->auditTrail->record(
            action: 'session.login',
            resourceType: 'user',
            resourceId: $event->user->id,
            result: 'success',
            user: $event->user,
        );
    }
}

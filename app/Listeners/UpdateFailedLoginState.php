<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditTrail;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UpdateFailedLoginState
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $email = Str::lower((string) Arr::get($event->credentials, 'email'));
        $user = $event->user instanceof User
            ? $event->user
            : User::query()->where('email', $email)->first();

        if ($user !== null) {
            $failedLoginAttempts = min(255, $user->failed_login_attempts + 1);

            $user->forceFill([
                'failed_login_attempts' => $failedLoginAttempts,
                'locked_until' => $failedLoginAttempts >= 5 ? now()->addMinutes(15) : $user->locked_until,
            ])->save();
        }

        $this->auditTrail->record(
            action: 'session.login_failed',
            resourceType: 'user',
            resourceId: $user?->id,
            result: 'failed',
            user: $user,
            reason: 'Invalid credentials or inactive account.',
        );
    }
}

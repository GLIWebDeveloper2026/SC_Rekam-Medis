<?php

namespace App\Data\Ai;

use App\Models\Patient;
use App\Models\User;

final readonly class ChatActorContext
{
    public function __construct(
        public User $user,
        public ?Patient $patient,
        public ?string $activeRole,
    ) {}

    public function isApprovedPatient(): bool
    {
        return $this->activeRole === 'patient' && $this->patient !== null;
    }

    public function can(string $permission): bool
    {
        return $this->user->hasPermission($permission);
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'status',
    'failed_login_attempts',
    'locked_until',
    'last_login_at',
    'disabled_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->locked_until === null || $this->locked_until->isPast());
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['id', 'valid_from', 'valid_until', 'assigned_by', 'assigned_at']);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('code', $permission))
            ->where(function ($query): void {
                $query->whereNull('user_roles.valid_from')->orWhere('user_roles.valid_from', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('user_roles.valid_until')->orWhere('user_roles.valid_until', '>=', now());
            })
            ->exists();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('code', $role)->exists();
    }

    public function activeRoleCode(): ?string
    {
        return $this->roles()->orderBy('roles.code')->value('code');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }
}

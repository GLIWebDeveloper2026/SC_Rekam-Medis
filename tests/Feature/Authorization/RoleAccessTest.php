<?php

namespace Tests\Feature\Authorization;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_open_clinical_workspace(): void
    {
        $user = $this->userWithPermission('doctor', 'clinical.view');

        $this->actingAs($user)
            ->get('/clinical-workspace')
            ->assertOk()
            ->assertSee('Ruang kerja klinis');
    }

    public function test_registration_staff_cannot_open_clinical_workspace(): void
    {
        $user = $this->userWithPermission('registration', 'patients.manage');

        $this->actingAs($user)
            ->get('/clinical-workspace')
            ->assertForbidden();

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $user->id,
            'action' => 'authorization.denied',
            'resource_type' => 'route',
            'result' => 'denied',
        ]);
    }

    private function userWithPermission(string $roleCode, string $permissionCode): User
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'code' => $roleCode,
            'name' => str($roleCode)->headline(),
        ]);
        $permission = Permission::query()->create([
            'code' => $permissionCode,
            'resource' => str($permissionCode)->before('.'),
            'action' => str($permissionCode)->after('.'),
        ]);

        $role->permissions()->attach($permission);
        $user->roles()->attach($role, [
            'id' => fake()->uuid(),
            'assigned_at' => now(),
        ]);

        return $user->refresh();
    }
}

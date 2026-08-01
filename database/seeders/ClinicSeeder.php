<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'dashboard.view', 'users.manage', 'patients.view', 'patients.manage', 'patients.safety-view',
            'schedules.view', 'queue.view', 'queue.manage', 'triage.manage', 'clinical.view', 'clinical.create',
            'prescriptions.create', 'pharmacy.manage', 'inventory.manage', 'record-copies.manage', 'record-copies.approve',
            'reports.view', 'audit.view', 'system.manage',
        ])->mapWithKeys(function (string $code): array {
            [$resource, $action] = explode('.', $code, 2);

            return [$code => Permission::query()->create(compact('code', 'resource', 'action'))];
        });

        $roles = [
            'owner' => ['dashboard.view', 'patients.view', 'reports.view', 'audit.view', 'users.manage', 'record-copies.approve'],
            'doctor' => ['dashboard.view', 'patients.view', 'patients.safety-view', 'queue.view', 'clinical.view', 'clinical.create', 'prescriptions.create', 'record-copies.approve'],
            'dentist' => ['dashboard.view', 'patients.view', 'patients.safety-view', 'queue.view', 'clinical.view', 'clinical.create', 'prescriptions.create', 'record-copies.approve'],
            'nurse' => ['dashboard.view', 'patients.view', 'patients.safety-view', 'queue.view', 'queue.manage', 'triage.manage', 'clinical.view'],
            'pharmacist' => ['dashboard.view', 'patients.safety-view', 'pharmacy.manage', 'inventory.manage'],
            'registration' => ['dashboard.view', 'patients.view', 'patients.manage', 'schedules.view', 'queue.view', 'queue.manage', 'record-copies.manage'],
            'system_admin' => ['dashboard.view', 'users.manage', 'audit.view', 'system.manage'],
        ];

        foreach ($roles as $code => $permissionCodes) {
            $role = Role::query()->create([
                'code' => $code,
                'name' => str($code)->replace('_', ' ')->title(),
            ]);
            $role->permissions()->sync(collect($permissionCodes)->map(fn (string $permission) => $permissions[$permission]->id));
        }

        $accounts = [
            ['owner', 'Pemilik Klinik', 'owner@sehatbersama.test'],
            ['doctor', 'dr. Bima Pratama', 'dokter@sehatbersama.test'],
            ['dentist', 'drg. Ayu Lestari', 'doktergigi@sehatbersama.test'],
            ['nurse', 'Ners. Rina Safitri', 'perawat@sehatbersama.test'],
            ['pharmacist', 'Apt. Dimas Nugraha', 'apoteker@sehatbersama.test'],
            ['registration', 'Maya Pendaftaran', 'pendaftaran@sehatbersama.test'],
            ['system_admin', 'Administrator Sistem', 'admin@sehatbersama.test'],
        ];

        foreach ($accounts as [$roleCode, $name, $email]) {
            $user = User::query()->create([
                'name' => $name,
                'username' => str($email)->before('@')->toString(),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('Klinik!2026'),
                'status' => 'active',
            ]);
            $user->roles()->attach(Role::query()->where('code', $roleCode)->valueOrFail('id'), [
                'id' => (string) Str::uuid(),
                'assigned_at' => now(),
            ]);
        }
    }
}

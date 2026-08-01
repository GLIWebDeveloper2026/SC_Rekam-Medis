<?php

namespace Tests\Feature\Auth;

use App\Models\PatientPortalAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_routes_are_disabled(): void
    {
        $this->assertNull(Route::getRoutes()->getByName('verification.notice'));
        $this->assertNull(Route::getRoutes()->getByName('verification.verify'));
        $this->assertNull(Route::getRoutes()->getByName('verification.send'));
    }

    public function test_unverified_patient_can_login_and_access_account_status(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'pasien@sehatbersama.test',
            'password' => Hash::make('Klinik!2026'),
        ]);
        $patientRole = Role::query()->create([
            'code' => 'patient',
            'name' => 'Patient',
        ]);
        $user->roles()->attach($patientRole, [
            'id' => (string) Str::uuid(),
            'assigned_at' => now(),
        ]);
        PatientPortalAccount::factory()->pending()->for($user)->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Klinik!2026',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertRedirectToRoute('patient-portal.status');
        $this->get('/patient-portal/account-status')->assertOk();
    }
}

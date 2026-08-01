<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_secrets_are_hidden_from_serialization(): void
    {
        $user = User::factory()->create()->forceFill([
            'two_factor_secret' => 'encrypted-secret',
            'two_factor_recovery_codes' => 'encrypted-recovery-codes',
        ]);

        $serializedUser = $user->toArray();

        $this->assertArrayNotHasKey('two_factor_secret', $serializedUser);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $serializedUser);
    }

    public function test_two_factor_authentication_routes_are_disabled(): void
    {
        $this->assertNull(Route::getRoutes()->getByName('two-factor.login'));
        $this->assertNull(Route::getRoutes()->getByName('two-factor.login.store'));
        $this->assertNull(Route::getRoutes()->getByName('two-factor.enable'));
    }

    public function test_user_with_existing_two_factor_configuration_can_login_without_challenge(): void
    {
        $user = User::factory()->create([
            'email' => 'dua.faktor@sehatbersama.test',
            'password' => Hash::make('Klinik!2026'),
            'two_factor_secret' => Crypt::encryptString('existing-secret'),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode(['recovery-code'], JSON_THROW_ON_ERROR)),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Klinik!2026',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }
}

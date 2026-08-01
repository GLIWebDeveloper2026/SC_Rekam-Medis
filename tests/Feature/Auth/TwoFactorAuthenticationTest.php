<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
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

    public function test_user_can_enable_and_confirm_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => now()->timestamp])
            ->post('/user/two-factor-authentication')
            ->assertSessionHas('status', 'two-factor-authentication-enabled');

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);

        $secret = Crypt::decrypt($user->two_factor_secret);
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => now()->timestamp])
            ->post('/user/confirmed-two-factor-authentication', ['code' => $code])
            ->assertSessionHas('status', 'two-factor-authentication-confirmed');

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_confirmed_two_factor_user_is_challenged_during_login(): void
    {
        $user = User::factory()->create([
            'email' => 'dua.faktor@sehatbersama.test',
            'password' => Hash::make('Klinik!2026'),
        ]);

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => now()->timestamp])
            ->post('/user/two-factor-authentication');

        $user->refresh()->forceFill(['two_factor_confirmed_at' => now()])->save();
        $recoveryCodes = json_decode(Crypt::decrypt($user->two_factor_recovery_codes), true, flags: JSON_THROW_ON_ERROR);

        $this->actingAsGuest();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Klinik!2026',
        ])->assertRedirect('/two-factor-challenge');

        $this->assertGuest();
        $this->get('/two-factor-challenge')
            ->assertOk()
            ->assertSee('Konfirmasi autentikasi dua faktor');

        $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCodes[0],
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => now()->timestamp])
            ->post('/user/two-factor-authentication');

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => now()->timestamp])
            ->delete('/user/two-factor-authentication')
            ->assertSessionHas('status', 'two-factor-authentication-disabled');

        $user->refresh();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }
}

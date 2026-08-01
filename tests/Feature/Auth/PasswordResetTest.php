<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_is_available(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Atur ulang kata sandi');
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('PasswordLama!2026'),
        ]);
        $token = Password::broker()->createToken($user);

        $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))
            ->assertOk()
            ->assertSee('Buat kata sandi baru');

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru!2026',
            'password_confirmation' => 'PasswordBaru!2026',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('PasswordBaru!2026', $user->fresh()->password));
    }
}

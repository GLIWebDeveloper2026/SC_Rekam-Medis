<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_account_security_screen(): void
    {
        $this->get('/account/security')->assertRedirect('/login');
    }

    public function test_account_security_screen_is_available(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => now()->timestamp])
            ->get('/account/security')
            ->assertOk()
            ->assertSee('Keamanan akun')
            ->assertSee('Profil dan identitas')
            ->assertDontSee('Autentikasi dua faktor')
            ->assertDontSee('Verifikasi email');
    }

    public function test_account_security_screen_requires_password_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/account/security')
            ->assertRedirect('/user/confirm-password');
    }

    public function test_user_can_update_profile_information(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'username' => 'nama.lama',
        ]);

        $this->actingAs($user)
            ->put('/user/profile-information', [
                'name' => 'Nama Baru',
                'username' => 'nama.baru',
                'email' => 'nama.baru@sehatbersama.test',
            ])->assertSessionHas('status', 'profile-information-updated');

        $updatedUser = $user->fresh();

        $this->assertSame('Nama Baru', $updatedUser->name);
        $this->assertSame('nama.baru', $updatedUser->username);
        $this->assertSame('nama.baru@sehatbersama.test', $updatedUser->email);
        Notification::assertNotSentTo($updatedUser, VerifyEmail::class);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('PasswordLama!2026'),
        ]);

        $this->actingAs($user)
            ->put('/user/password', [
                'current_password' => 'PasswordLama!2026',
                'password' => 'PasswordBaru!2026',
                'password_confirmation' => 'PasswordBaru!2026',
            ])->assertSessionHas('status', 'password-updated');

        $this->assertTrue(Hash::check('PasswordBaru!2026', $user->fresh()->password));
    }
}

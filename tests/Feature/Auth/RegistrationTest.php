<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_available(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Buat akun staf');
    }

    public function test_new_user_can_register_with_email_and_password(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Dokter Baru',
            'username' => 'dokter.baru',
            'email' => 'dokter.baru@sehatbersama.test',
            'password' => 'Klinik!2026',
            'password_confirmation' => 'Klinik!2026',
        ])->assertRedirect('/dashboard');

        $user = User::query()->where('email', 'dokter.baru@sehatbersama.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('dokter.baru', $user->username);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_requires_a_unique_username(): void
    {
        User::factory()->create(['username' => 'petugas.satu']);

        $this->post('/register', [
            'name' => 'Petugas Dua',
            'username' => 'petugas.satu',
            'email' => 'petugas.dua@sehatbersama.test',
            'password' => 'Klinik!2026',
            'password_confirmation' => 'Klinik!2026',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }
}

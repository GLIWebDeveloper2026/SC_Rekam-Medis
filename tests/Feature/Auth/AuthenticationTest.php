<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_is_available(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke sistem klinik');
    }

    public function test_active_user_can_login_and_open_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'dokter@sehatbersama.test',
            'password' => Hash::make('Klinik!2026'),
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Klinik!2026',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
    }

    public function test_disabled_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'nonaktif@sehatbersama.test',
            'password' => Hash::make('Klinik!2026'),
            'status' => 'disabled',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Klinik!2026',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
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

    public function test_login_submission_is_handled_by_fortify(): void
    {
        $route = Route::getRoutes()->getByName('login.store');

        $this->assertNotNull($route);
        $this->assertSame(
            'Laravel\\Fortify\\Http\\Controllers\\AuthenticatedSessionController@store',
            $route->getActionName(),
        );
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

    public function test_failed_login_updates_security_state_and_audit_trail(): void
    {
        $user = User::factory()->create([
            'email' => 'gagal@sehatbersama.test',
            'password' => Hash::make('Klinik!2026'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'salah-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, $user->fresh()->failed_login_attempts);
        $this->assertTrue(AuditEvent::query()
            ->where('action', 'session.login_failed')
            ->where('resource_id', $user->id)
            ->where('result', 'failed')
            ->exists());
    }

    public function test_fifth_failed_login_locks_the_account_for_fifteen_minutes(): void
    {
        $user = User::factory()->create([
            'email' => 'terkunci@sehatbersama.test',
            'password' => Hash::make('Klinik!2026'),
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'salah-'.$attempt,
            ])->assertSessionHasErrors('email');
        }

        $lockedUser = $user->fresh();

        $this->assertSame(5, $lockedUser->failed_login_attempts);
        $this->assertNotNull($lockedUser->locked_until);
        $this->assertTrue($lockedUser->locked_until->between(now()->addMinutes(14), now()->addMinutes(16)));
    }

    public function test_successful_login_resets_security_state_and_records_audit(): void
    {
        $user = User::factory()->create([
            'email' => 'pulih@sehatbersama.test',
            'password' => Hash::make('Klinik!2026'),
            'failed_login_attempts' => 3,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Klinik!2026',
        ])->assertRedirect('/dashboard');

        $authenticatedUser = $user->fresh();

        $this->assertSame(0, $authenticatedUser->failed_login_attempts);
        $this->assertNull($authenticatedUser->locked_until);
        $this->assertNotNull($authenticatedUser->last_login_at);
        $this->assertTrue(AuditEvent::query()
            ->where('action', 'session.login')
            ->where('resource_id', $user->id)
            ->where('result', 'success')
            ->exists());
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
        $this->assertTrue(AuditEvent::query()
            ->where('action', 'session.logout')
            ->where('resource_id', $user->id)
            ->where('result', 'success')
            ->exists());
    }
}

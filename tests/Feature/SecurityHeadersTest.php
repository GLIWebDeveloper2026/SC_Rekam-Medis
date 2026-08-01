<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_csp_uses_a_nonce_without_unsafe_inline(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("'nonce-", $policy);
        $this->assertStringNotContainsString("'unsafe-inline'", $policy);
        $this->assertMatchesRegularExpression('/<script[^>]+nonce="[^"]+"/', $response->getContent());
    }

    public function test_strict_csp_pages_do_not_use_alpine_x_show_inline_styles(): void
    {
        $this->get('/')->assertOk()->assertDontSee('x-show', false);

        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertDontSee('x-show', false);
    }

    public function test_authenticated_pages_disable_caching_and_send_security_headers(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}

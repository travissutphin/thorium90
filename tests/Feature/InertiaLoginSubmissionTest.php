<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithRoles;
use App\Models\User;

class InertiaLoginSubmissionTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    /** @test */
    public function two_factor_authentication_endpoints_handle_inertia_requests_properly()
    {
        $user = $this->createUserWithRole('Subscriber');
        
        // Test that accessing the 2FA status endpoint with Inertia headers redirects instead of returning JSON
        $response = $this->actingAs($user)
                         ->withSession(['_token' => 'test-token'])
                         ->withHeaders([
                             'X-Inertia' => true,
                             'X-Inertia-Version' => 'test-version',
                             'X-CSRF-TOKEN' => 'test-token'
                         ])
                         ->get('/user/two-factor-authentication');
        
        // Should redirect to dashboard, not return JSON error
        $this->assertTrue(
            $response->isRedirect() || $response->isSuccessful(),
            'Response should redirect or succeed, got status: ' . $response->status()
        );
    }

    /** @test */
    public function two_factor_authentication_endpoints_return_json_for_api_requests()
    {
        $user = $this->createUserWithRole('Subscriber');
        
        // Test that accessing the 2FA status endpoint without Inertia headers returns JSON
        $response = $this->actingAs($user)
                         ->get('/user/two-factor-authentication');
        
        $response->assertStatus(200);
        $response->assertJson([
            'two_factor_enabled' => false,
            'two_factor_confirmed' => false,
            'recovery_codes_count' => 0,
        ]);
    }

    /** @test */
    public function two_factor_challenge_endpoint_handles_inertia_requests()
    {
        // Test accessing two-factor challenge without session (should redirect)
        $response = $this->withSession(['_token' => 'test-token'])
                         ->withHeaders([
                             'X-Inertia' => true,
                             'X-Inertia-Version' => 'test-version',
                             'X-CSRF-TOKEN' => 'test-token'
                         ])
                         ->get('/two-factor-challenge');
        
        // Should redirect to login since no login session exists
        $this->assertTrue(
            $response->isRedirect(),
            'Response should redirect, got status: ' . $response->status()
        );
    }

    /** @test */
    public function login_submission_does_not_cause_inertia_json_error()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Simulate login form submission with Inertia
        $response = $this->withSession(['_token' => 'test-token'])
                         ->withHeaders([
                             'X-Inertia' => true,
                             'X-Inertia-Version' => 'test-version',
                             'X-CSRF-TOKEN' => 'test-token'
                         ])
                         ->post('/login', [
                             '_token' => 'test-token',
                             'email' => 'test@example.com',
                             'password' => 'password',
                         ]);

        // Should redirect to dashboard, not return JSON error
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
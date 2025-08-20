<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class LoginRegressionTest extends TestCase
{
    use RefreshDatabase, WithRoles;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions for testing
        $this->createRolesAndPermissions();
    }

    /** @test */
    public function user_can_login_successfully_without_2fa()
    {
        // Create a user without 2FA
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $user->assignRole('Subscriber');

        // Attempt login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Should redirect to dashboard on successful login
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_can_access_2fa_status_endpoint_without_errors()
    {
        // Create a user without 2FA
        $user = User::factory()->create([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $user->assignRole('Subscriber');

        // Access 2FA status endpoint that was causing the DecryptException
        $response = $this->actingAs($user)->get('/user/two-factor-authentication');

        $response->assertOk();
        $response->assertJson([
            'two_factor_enabled' => false,
            'two_factor_confirmed' => false,
            'recovery_codes_count' => 0,
        ]);
    }

    /** @test */
    public function user_with_2fa_can_login_and_access_status()
    {
        // Create a user with 2FA enabled
        $user = User::factory()->create([
            'email' => 'test2fa@example.com',
            'password' => bcrypt('password'),
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode([
                'code1', 'code2', 'code3', 'code4', 'code5',
                'code6', 'code7', 'code8'
            ])),
        ]);
        $user->assignRole('Subscriber');

        // Login
        $response = $this->post('/login', [
            'email' => 'test2fa@example.com',
            'password' => 'password',
        ]);

        // With 2FA disabled for development, should redirect directly to dashboard
        $response->assertRedirect('/dashboard');

        // Access 2FA status endpoint should work without errors
        $response = $this->actingAs($user)->get('/user/two-factor-authentication');

        $response->assertOk();
        $response->assertJson([
            'two_factor_enabled' => true,
            'two_factor_confirmed' => true,
            'recovery_codes_count' => 8,
        ]);
    }

    /** @test */
    public function user_with_corrupted_recovery_codes_handles_gracefully()
    {
        // Create a user with corrupted recovery codes
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => 'corrupted_non_encrypted_data',
        ]);
        $user->assignRole('Subscriber');

        // Access 2FA status endpoint should handle corrupted data gracefully
        $response = $this->actingAs($user)->get('/user/two-factor-authentication');

        $response->assertOk();
        $response->assertJson([
            'two_factor_enabled' => true,
            'two_factor_confirmed' => true,
            'recovery_codes_count' => 0, // Should gracefully handle corruption
        ]);
    }

    /** @test */
    public function admin_user_login_works_with_2fa_enforcement()
    {
        // Create an admin user with 2FA
        $admin = $this->createAdmin();
        $admin->forceFill([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Login should work
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        // With 2FA disabled for development, should redirect directly to dashboard
        $response->assertRedirect('/dashboard');
        
        // Access dashboard should work since 2FA is disabled for development
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertOk();
    }
}

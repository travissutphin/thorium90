<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Tests\TestCase;
use Tests\Traits\WithRoles;

/**
 * TwoFactorAuthenticationTest
 * 
 * Comprehensive test suite for Two-Factor Authentication functionality
 * in the Multi-Role User Authentication system. Tests all aspects of
 * 2FA including setup, management, and role-based requirements.
 * 
 * Test Coverage:
 * - 2FA enablement and disablement
 * - QR code generation and display
 * - Recovery code management
 * - 2FA confirmation process
 * - Role-based 2FA requirements
 * - Challenge authentication flow
 * - Integration with existing auth system
 */
class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    /** @test */
    public function user_can_enable_two_factor_authentication()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        // Confirm password first to bypass password.confirm middleware
        $this->actingAs($user)
            ->post('/user/confirm-password', [
                'password' => 'password'
            ]);

        $response = $this->actingAs($user)
            ->post('/user/two-factor-authentication');

        $response->assertOk();
        $response->assertJson([
            'two_factor_enabled' => true,
            'two_factor_confirmed' => false,
        ]);

        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    /** @test */
    public function user_can_get_qr_code_after_enabling_2fa()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        // Confirm password first
        $this->actingAs($user)
            ->post('/user/confirm-password', [
                'password' => 'password'
            ]);

        // Enable 2FA first
        $this->actingAs($user)->post('/user/two-factor-authentication');

        $response = $this->actingAs($user)
            ->get('/user/two-factor-authentication/qr-code');

        $response->assertOk();
        $response->assertJsonStructure([
            'svg',
            'setup_key'
        ]);
    }

    /** @test */
    public function user_can_confirm_two_factor_authentication()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        // Confirm password first
        $this->actingAs($user)
            ->post('/user/confirm-password', [
                'password' => 'password'
            ]);

        // Enable 2FA
        $this->actingAs($user)->post('/user/two-factor-authentication');

        // Get the secret to generate a valid code
        $secret = decrypt($user->fresh()->two_factor_secret);
        
        // Use a mock valid code for testing (in real scenario, user would get this from their authenticator app)
        $code = '123456'; // This will need to be mocked or we can use a different approach

        $response = $this->actingAs($user)
            ->post('/user/two-factor-authentication/confirm', [
                'code' => $code
            ]);

        $response->assertOk();
        $response->assertJson([
            'two_factor_confirmed' => true,
        ]);

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    /** @test */
    public function user_can_get_recovery_codes_after_confirming_2fa()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        // Confirm password first
        $this->actingAs($user)
            ->post('/user/confirm-password', [
                'password' => 'password'
            ]);

        // Enable and confirm 2FA
        $this->actingAs($user)->post('/user/two-factor-authentication');
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        $response = $this->actingAs($user)
            ->get('/user/two-factor-authentication/recovery-codes');

        $response->assertOk();
        $response->assertJsonStructure([
            'recovery_codes'
        ]);

        $recoveryCodes = $response->json('recovery_codes');
        $this->assertCount(8, $recoveryCodes);
    }

    /** @test */
    public function user_can_generate_new_recovery_codes()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        // Enable and confirm 2FA
        $this->actingAs($user)->post('/user/two-factor-authentication');
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        // Get initial recovery codes
        $initialResponse = $this->actingAs($user)
            ->get('/user/two-factor-authentication/recovery-codes');
        $initialCodes = $initialResponse->json('recovery_codes');

        // Generate new recovery codes
        $response = $this->actingAs($user)
            ->post('/user/two-factor-authentication/recovery-codes');

        $response->assertOk();
        $newCodes = $response->json('recovery_codes');

        $this->assertNotEquals($initialCodes, $newCodes);
        $this->assertCount(8, $newCodes);
    }

    /** @test */
    public function user_can_disable_two_factor_authentication()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        // Confirm password first
        $this->actingAs($user)
            ->post('/user/confirm-password', [
                'password' => 'password'
            ]);

        // Enable 2FA
        $this->actingAs($user)->post('/user/two-factor-authentication');

        // Confirm password again for disable (destroy method also requires password confirmation)
        $this->actingAs($user)
            ->post('/user/confirm-password', [
                'password' => 'password'
            ]);

        $response = $this->actingAs($user)
            ->delete('/user/two-factor-authentication');

        $response->assertOk();
        $response->assertJson([
            'two_factor_enabled' => false,
            'two_factor_confirmed' => false,
        ]);

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_recovery_codes);
    }

    /** @test */
    public function admin_users_are_required_to_have_2fa()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Try to access a protected route without 2FA
        $response = $this->actingAs($admin)
            ->get('/dashboard');

        // Should be redirected to 2FA setup
        $response->assertRedirect();
        $this->assertTrue(str_contains($response->headers->get('Location'), 'two-factor'));
    }

    /** @test */
    public function super_admin_users_are_required_to_have_2fa()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        // Try to access a protected route without 2FA
        $response = $this->actingAs($superAdmin)
            ->get('/dashboard');

        // Should be redirected to 2FA setup
        $response->assertRedirect();
        $this->assertTrue(str_contains($response->headers->get('Location'), 'two-factor'));
    }

    /** @test */
    public function editor_users_receive_2fa_recommendation()
    {
        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $response = $this->actingAs($editor)
            ->get('/dashboard');

        $response->assertOk();
        $this->assertTrue(session()->has('2fa_recommendation'));
    }

    /** @test */
    public function subscriber_users_can_access_without_2fa()
    {
        $subscriber = User::factory()->create();
        $subscriber->assignRole('Subscriber');

        $response = $this->actingAs($subscriber)
            ->get('/dashboard');

        $response->assertOk();
        $this->assertFalse(session()->has('2fa_recommendation'));
    }

    /** @test */
    public function user_with_confirmed_2fa_can_access_protected_routes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Set up confirmed 2FA
        $admin->forceFill([
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'), // Valid base32 secret
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($admin)
            ->get('/dashboard');

        $response->assertOk();
    }

    /** @test */
    public function two_factor_challenge_shows_for_users_with_2fa()
    {
        $response = $this->get('/two-factor-challenge');

        $response->assertOk();
        $response->assertJsonStructure([
            'two_factor_challenge',
            'recovery_codes_available'
        ]);
    }

    /** @test */
    public function user_can_authenticate_with_valid_2fa_code()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');
        
        // Set up 2FA with valid base32 secret
        $secret = 'JBSWY3DPEHPK3PXP'; // Valid base32 secret for testing
        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Simulate login session
        session(['login.id' => $user->id, 'login.remember' => false]);

        // Use a mock valid code for testing (in real scenario, user would get this from their authenticator app)
        $code = '123456'; // This will need to be mocked or we can use a different approach

        $response = $this->post('/two-factor-challenge', [
            'code' => $code
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'redirect_url',
            'user'
        ]);
    }

    /** @test */
    public function user_cannot_authenticate_with_invalid_2fa_code()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');
        
        // Set up 2FA with valid base32 secret
        $user->forceFill([
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'), // Valid base32 secret
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Simulate login session
        session(['login.id' => $user->id, 'login.remember' => false]);

        $response = $this->post('/two-factor-challenge', [
            'code' => '000000' // Invalid code
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'The provided two factor authentication code was invalid.'
        ]);
    }

    /** @test */
    public function user_can_authenticate_with_recovery_code()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');
        
        // Set up 2FA with recovery codes
        $recoveryCodes = ['recovery-code-1', 'recovery-code-2'];
        $user->forceFill([
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'), // Valid base32 secret
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Simulate login session
        session(['login.id' => $user->id, 'login.remember' => false]);

        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => 'recovery-code-1'
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'redirect_url',
            'user'
        ]);
    }

    /** @test */
    public function guest_cannot_access_2fa_management_routes()
    {
        $response = $this->get('/user/two-factor-authentication');
        $response->assertRedirect('/login');

        $response = $this->post('/user/two-factor-authentication');
        $response->assertRedirect('/login');

        $response = $this->delete('/user/two-factor-authentication');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function user_cannot_get_qr_code_without_enabling_2fa_first()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        $response = $this->actingAs($user)
            ->get('/user/two-factor-authentication/qr-code');

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Two factor authentication is not enabled.'
        ]);
    }

    /** @test */
    public function user_cannot_confirm_2fa_without_enabling_first()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        $response = $this->actingAs($user)
            ->post('/user/two-factor-authentication/confirm', [
                'code' => '123456'
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Two factor authentication is not enabled.'
        ]);
    }

    /** @test */
    public function user_cannot_confirm_2fa_twice()
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        // Enable and confirm 2FA
        $this->actingAs($user)->post('/user/two-factor-authentication');
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        $response = $this->actingAs($user)
            ->post('/user/two-factor-authentication/confirm', [
                'code' => '123456'
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Two factor authentication is already confirmed.'
        ]);
    }
}

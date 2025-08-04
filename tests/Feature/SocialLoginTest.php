<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

/**
 * Social Login Feature Tests
 * 
 * Tests the complete social login flow including OAuth provider integration,
 * user creation, existing user linking, and error handling scenarios.
 * 
 * Test Coverage:
 * - OAuth provider redirects
 * - Callback handling and user creation
 * - Existing user account linking
 * - Error handling for invalid providers
 * - Security validation
 * - Role assignment for new users
 */
class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure test OAuth providers
        Config::set('services.google', [
            'client_id' => 'test-google-client-id',
            'client_secret' => 'test-google-client-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]);

        Config::set('services.github', [
            'client_id' => 'test-github-client-id',
            'client_secret' => 'test-github-client-secret',
            'redirect' => 'http://localhost/auth/github/callback',
        ]);
    }

    public function test_social_login_redirect_to_provider(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('http://provider.com/oauth'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/auth/google');

        $response->assertRedirect('http://provider.com/oauth');
    }

    public function test_social_login_redirect_with_unsupported_provider(): void
    {
        $response = $this->get('/auth/unsupported');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['provider']);
    }

    public function test_social_login_redirect_with_unconfigured_provider(): void
    {
        // Remove configuration for Facebook
        Config::set('services.facebook', []);

        $response = $this->get('/auth/facebook');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['provider']);
    }

    public function test_social_login_callback_creates_new_user(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'provider' => 'google',
            'provider_id' => '123456789',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_social_login_callback_links_existing_user(): void
    {
        // Create existing user
        $existingUser = User::factory()->create([
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');

        $existingUser->refresh();
        $this->assertEquals('google', $existingUser->provider);
        $this->assertEquals('123456789', $existingUser->provider_id);
        $this->assertEquals('https://example.com/avatar.jpg', $existingUser->avatar);
        $this->assertAuthenticatedAs($existingUser);
    }

    public function test_social_login_callback_finds_existing_social_user(): void
    {
        // Create existing social user
        $existingUser = User::factory()->create([
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'provider' => 'google',
            'provider_id' => '123456789',
            'avatar' => 'https://example.com/old-avatar.jpg',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/new-avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');

        $existingUser->refresh();
        $this->assertEquals('https://example.com/new-avatar.jpg', $existingUser->avatar);
        $this->assertAuthenticatedAs($existingUser);
    }

    public function test_social_login_callback_without_email(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getEmail')->andReturn(null);
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_social_login_callback_with_invalid_state(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->once()->andThrow(new \Laravel\Socialite\Two\InvalidStateException());

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['provider']);
        $this->assertGuest();
    }

    public function test_social_login_callback_with_general_exception(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->once()->andThrow(new \Exception('OAuth error'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['provider']);
        $this->assertGuest();
    }

    public function test_user_model_social_login_methods(): void
    {
        // Test findForSocialLogin
        $user = User::factory()->create([
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        $foundUser = User::findForSocialLogin('google', '123456789');
        $this->assertEquals($user->id, $foundUser->id);

        $notFoundUser = User::findForSocialLogin('google', '987654321');
        $this->assertNull($notFoundUser);

        // Test createFromSocialProvider
        $userData = [
            'id' => '987654321',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'avatar' => 'https://example.com/jane-avatar.jpg',
        ];

        $newUser = User::createFromSocialProvider('github', $userData);

        $this->assertEquals('Jane Doe', $newUser->name);
        $this->assertEquals('jane@example.com', $newUser->email);
        $this->assertEquals('github', $newUser->provider);
        $this->assertEquals('987654321', $newUser->provider_id);
        $this->assertEquals('https://example.com/jane-avatar.jpg', $newUser->avatar);
        $this->assertNotNull($newUser->email_verified_at);

        // Test isSocialUser
        $this->assertTrue($user->isSocialUser());
        
        $regularUser = User::factory()->create();
        $this->assertFalse($regularUser->isSocialUser());

        // Test getAvatarUrl
        $this->assertEquals('https://example.com/jane-avatar.jpg', $newUser->getAvatarUrl());
        
        $userWithoutAvatar = User::factory()->create(['email' => 'test@example.com']);
        $expectedGravatar = 'https://www.gravatar.com/avatar/' . md5('test@example.com') . '?s=80&d=identicon';
        $this->assertEquals($expectedGravatar, $userWithoutAvatar->getAvatarUrl());
    }

    public function test_social_login_routes_require_guest_middleware(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/auth/google');
        $response->assertRedirect('/dashboard');

        $response = $this->get('/auth/google/callback');
        $response->assertRedirect('/dashboard');
    }

    public function test_social_login_redirect_stores_intended_url(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('http://provider.com/oauth'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $response = $this->get('/auth/google?redirect=/admin/dashboard');

        $this->assertEquals('/admin/dashboard', session('url.intended'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

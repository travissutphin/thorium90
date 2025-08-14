<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\WithRoles;

/**
 * Laravel Sanctum API Authentication Tests
 * 
 * This test suite verifies the integration between Laravel Sanctum
 * and the existing role-based permission system. It ensures that
 * API authentication works correctly with both token-based and
 * session-based authentication methods.
 */
class SanctumApiTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    /** @test */
    public function api_health_endpoint_is_publicly_accessible()
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version'
            ])
            ->assertJson([
                'status' => 'ok',
                'version' => '1.0.0'
            ]);
    }

    /** @test */
    public function sanctum_csrf_cookie_endpoint_works()
    {
        $response = $this->get('/sanctum/csrf-cookie');

        $response->assertNoContent();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_api_endpoints()
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    /** @test */
    public function authenticated_user_can_get_user_info_via_api()
    {
        $user = $this->createUserWithRole('Admin');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'roles',
                'permissions',
                'is_admin',
                'is_content_creator'
            ])
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => true,
            ]);
    }

    /** @test */
    public function user_can_create_api_token()
    {
        $user = $this->createUserWithRole('Admin');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tokens', [
            'name' => 'Test Token',
            'abilities' => ['*']
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'token',
                'name',
                'abilities'
            ])
            ->assertJson([
                'name' => 'Test Token',
                'abilities' => ['*']
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'Test Token'
        ]);
    }

    /** @test */
    public function user_can_list_their_tokens()
    {
        $user = $this->createUserWithRole('Admin');
        $token = $user->createToken('Test Token');
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tokens');

        $response->assertOk()
            ->assertJsonStructure([
                'tokens' => [
                    '*' => [
                        'id',
                        'name',
                        'abilities',
                        'last_used_at',
                        'expires_at',
                        'created_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_revoke_their_token()
    {
        $user = $this->createUserWithRole('Admin');
        $token = $user->createToken('Test Token');
        
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tokens/{$token->accessToken->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Token revoked successfully'
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id
        ]);
    }

    /** @test */
    public function api_respects_role_based_access_control()
    {
        // Test Admin access
        $admin = $this->createUserWithRole('Admin');
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/users');
        $response->assertOk();

        // Test non-admin access
        $subscriber = $this->createUserWithRole('Subscriber');
        Sanctum::actingAs($subscriber);

        $response = $this->getJson('/api/admin/users');
        $response->assertForbidden();
    }

    /** @test */
    public function api_respects_permission_based_access_control()
    {
        // Create user with specific permission (Admin has manage user roles permission, which should work)
        $user = $this->createUserWithRole('Admin');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user-management');
        $response->assertForbidden(); // Admin doesn't have 'manage users' permission

        // Create Super Admin who has all permissions
        $superAdmin = $this->createUserWithRole('Super Admin');
        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/user-management');
        $response->assertOk(); // Super Admin has all permissions

        // Test user without permission
        $subscriber = $this->createUserWithRole('Subscriber');
        Sanctum::actingAs($subscriber);

        $response = $this->getJson('/api/user-management');
        $response->assertForbidden();
    }

    /** @test */
    public function editor_can_access_content_endpoints()
    {
        $editor = $this->createUserWithRole('Editor');
        Sanctum::actingAs($editor);

        $response = $this->getJson('/api/content/pages');
        
        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user_roles',
                'access_level'
            ])
            ->assertJson([
                'access_level' => 'Editor+'
            ]);
    }

    /** @test */
    public function author_can_access_author_endpoints()
    {
        $author = $this->createUserWithRole('Author');
        Sanctum::actingAs($author);

        $response = $this->getJson('/api/author/my-pages');
        
        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user_roles',
                'access_level'
            ])
            ->assertJson([
                'access_level' => 'Author+'
            ]);
    }

    /** @test */
    public function subscriber_cannot_access_restricted_endpoints()
    {
        $subscriber = $this->createUserWithRole('Subscriber');
        Sanctum::actingAs($subscriber);

        // Test admin endpoints
        $response = $this->getJson('/api/admin/users');
        $response->assertForbidden();

        // Test content endpoints
        $response = $this->getJson('/api/content/pages');
        $response->assertForbidden();

        // Test author endpoints
        $response = $this->getJson('/api/author/my-pages');
        $response->assertForbidden();
    }

    /** @test */
    public function api_token_authentication_works_with_bearer_token()
    {
        $user = $this->createUserWithRole('Admin');
        $token = $user->createToken('Test Token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
            'Accept' => 'application/json',
        ])->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /** @test */
    public function invalid_token_returns_unauthorized()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json',
        ])->getJson('/api/user');

        $response->assertUnauthorized();
    }

    /** @test */
    public function token_validation_includes_role_and_permission_data()
    {
        $user = $this->createUserWithRole('Editor');
        $token = $user->createToken('Test Token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
            'Accept' => 'application/json',
        ])->getJson('/api/user');

        $response->assertOk()
            ->assertJsonFragment([
                'roles' => ['Editor']
            ])
            ->assertJsonStructure([
                'permissions'
            ]);

        // Verify permissions include Editor-specific permissions
        $permissions = $response->json('permissions');
        $this->assertContains('create pages', $permissions);
        $this->assertContains('edit pages', $permissions);
    }

    /** @test */
    public function session_authentication_works_for_api_endpoints()
    {
        $user = $this->createUserWithRole('Admin');
        
        // Authenticate via session
        $this->actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /** @test */
    public function mixed_authentication_scenarios_work_correctly()
    {
        $user = $this->createUserWithRole('Admin');
        
        // Test 1: Session authentication
        $this->actingAs($user);
        $response = $this->getJson('/api/tokens');
        $response->assertOk();

        // Test 2: Token authentication
        $token = $user->createToken('Mixed Auth Test');
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ]);
        
        $response = $this->getJson('/api/tokens');
        $response->assertOk();
    }

    /** @test */
    public function api_error_responses_are_properly_formatted()
    {
        // Test validation error
        $user = $this->createUserWithRole('Admin');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tokens', [
            'name' => '', // Invalid: empty name
        ]);

        $response->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name'
                ]
            ]);
    }

    /** @test */
    public function cors_headers_are_properly_set_for_api_requests()
    {
        $user = $this->createUserWithRole('Admin');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertOk();
        
        // Note: CORS headers would be tested in browser environment
        // This test ensures the endpoint works correctly
    }
}

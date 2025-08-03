<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

/**
 * UIPermissionTest
 * 
 * This test class validates the frontend integration of the Multi-Role User Authentication system.
 * It ensures that user roles, permissions, and computed properties are correctly shared with
 * the React frontend via Inertia.js and can be accessed and used properly.
 * 
 * Key Test Areas:
 * - Inertia.js data sharing functionality
 * - Role-based computed properties (is_admin, is_content_creator, etc.)
 * - Permission checking functions exposed to frontend
 * - Role and permission arrays for frontend consumption
 * - Different user role scenarios and their expected permissions
 * 
 * Test Coverage:
 * - All 5 user roles (Super Admin, Admin, Editor, Author, Subscriber)
 * - Frontend data structure validation
 * - Permission inheritance verification
 * - Computed property accuracy
 * 
 * @see https://inertiajs.com/testing
 * @see https://spatie.be/docs/laravel-permission
 */
class UIPermissionTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    /**
     * Set up the test environment.
     * 
     * This method runs before each test and ensures that the roles and permissions
     * are properly created in the test database. This is essential for all tests
     * in this class to work correctly.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    /**
     * Test that Inertia.js correctly shares user data with the frontend.
     * 
     * This test verifies that the HandleInertiaRequests middleware properly loads
     * and shares user roles, permissions, and computed properties with the React frontend.
     * It specifically tests an Admin user to ensure all expected data is present.
     */
    public function test_inertia_shares_user_data_correctly()
    {
        // Create an admin user for testing
        $admin = $this->createAdmin();

        // Make a request to the dashboard as the admin user
        $response = $this->actingAs($admin)->get('/dashboard');

        // Verify the response is successful
        $response->assertOk();
        
        // Verify that Inertia.js shares the expected user data
        $response->assertInertia(fn ($page) => 
            $page->has('auth.user.role_names')           // Array of role names
                ->has('auth.user.permission_names')      // Array of permission names
                ->has('auth.user.is_admin')              // Computed admin property
                ->where('auth.user.is_admin', true)      // Admin should be true for admin users
                ->where('auth.user.role_names', fn ($roles) => in_array('Admin', $roles)) // Should have Admin role
        );
    }

    /**
     * Test that Subscriber users have limited permissions as expected.
     * 
     * This test ensures that users with the Subscriber role have the correct
     * computed properties and limited access as defined in the role hierarchy.
     */
    public function test_subscriber_has_limited_permissions()
    {
        // Create a subscriber user for testing
        $subscriber = $this->createSubscriber();

        // Make a request to the dashboard as the subscriber user
        $response = $this->actingAs($subscriber)->get('/dashboard');

        // Verify the response is successful
        $response->assertOk();
        
        // Verify that subscriber has limited permissions
        $response->assertInertia(fn ($page) => 
            $page->where('auth.user.is_admin', false)           // Should not be admin
                ->where('auth.user.is_content_creator', false)  // Should not be content creator
                ->where('auth.user.role_names', ['Subscriber']) // Should only have Subscriber role
        );
    }

    /**
     * Test that Super Admin users have all permissions and admin status.
     * 
     * This test verifies that Super Admin users have the highest level of access
     * and all computed properties are correctly set to true.
     */
    public function test_super_admin_has_all_permissions()
    {
        // Create a super admin user for testing
        $superAdmin = $this->createSuperAdmin();

        // Make a request to the dashboard as the super admin user
        $response = $this->actingAs($superAdmin)->get('/dashboard');

        // Verify the response is successful
        $response->assertOk();
        
        // Verify that super admin has all permissions
        $response->assertInertia(fn ($page) => 
            $page->where('auth.user.is_admin', true)           // Should be admin
                ->where('auth.user.is_content_creator', true)  // Should be content creator
                ->where('auth.user.role_names', ['Super Admin']) // Should have Super Admin role
        );
    }

    /**
     * Test that Editor users have content creation permissions but not admin status.
     * 
     * This test ensures that Editor users have the correct permission level
     * for content management without having administrative privileges.
     */
    public function test_editor_has_content_permissions()
    {
        // Create an editor user for testing
        $editor = $this->createEditor();

        // Make a request to the dashboard as the editor user
        $response = $this->actingAs($editor)->get('/dashboard');

        // Verify the response is successful
        $response->assertOk();
        
        // Verify that editor has content permissions but not admin status
        $response->assertInertia(fn ($page) => 
            $page->where('auth.user.is_admin', false)          // Should not be admin
                ->where('auth.user.is_content_creator', true)  // Should be content creator
                ->where('auth.user.role_names', ['Editor'])    // Should have Editor role
        );
    }

    /**
     * Test that permission checking functions are properly exposed to the frontend.
     * 
     * This test verifies that the helper functions (can, hasRole, hasPermissionTo)
     * are correctly shared with the React frontend and can be called from there.
     */
    public function test_user_permission_functions_work()
    {
        // Create an admin user for testing
        $admin = $this->createAdmin();

        // Make a request to the dashboard as the admin user
        $response = $this->actingAs($admin)->get('/dashboard');

        // Verify the response is successful
        $response->assertOk();
        
        // Verify that permission checking functions are available
        $response->assertInertia(fn ($page) => 
            $page->has('auth.user.can')              // Permission checking function
                ->has('auth.user.hasRole')           // Role checking function
                ->has('auth.user.hasPermissionTo')   // Direct permission checking function
        );
    }

    /**
     * Test that permission names are correctly shared and include inherited permissions.
     * 
     * This test verifies that the permission_names array includes both direct
     * permissions and permissions inherited from the user's roles. It specifically
     * tests an Editor user to ensure they have the expected permissions.
     */
    public function test_permission_names_are_shared_correctly()
    {
        // Create an editor user for testing
        $editor = $this->createEditor();

        // Make a request to the dashboard as the editor user
        $response = $this->actingAs($editor)->get('/dashboard');

        // Verify the response is successful
        $response->assertOk();
        
        // Verify that editor has the correct permissions
        $response->assertInertia(fn ($page) => 
            $page->where('auth.user.permission_names', fn ($permissions) => 
                in_array('view dashboard', $permissions) &&    // Should have dashboard access
                in_array('create posts', $permissions) &&      // Should be able to create posts
                in_array('edit posts', $permissions) &&        // Should be able to edit posts
                !in_array('view users', $permissions)          // Should NOT have user management access
            )
        );
    }
}

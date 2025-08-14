<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\WithRoles;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserHasAnyRole;
use App\Http\Middleware\EnsureUserHasAnyPermission;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    public function test_ensure_user_has_role_middleware_allows_correct_role()
    {
        $admin = $this->createAdmin();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $admin);

        $middleware = new EnsureUserHasRole();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'Admin');

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_ensure_user_has_role_middleware_denies_incorrect_role()
    {
        $subscriber = $this->createSubscriber();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $subscriber);

        $middleware = new EnsureUserHasRole();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'Admin');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_ensure_user_has_role_middleware_redirects_guests()
    {
        $request = Request::create('/test');
        $request->setUserResolver(fn() => null);

        $middleware = new EnsureUserHasRole();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'Admin');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContains('login', $response->headers->get('Location'));
    }

    public function test_ensure_user_has_permission_middleware_allows_correct_permission()
    {
        $admin = $this->createAdmin();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $admin);

        $middleware = new EnsureUserHasPermission();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'view users');

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_ensure_user_has_permission_middleware_denies_incorrect_permission()
    {
        $subscriber = $this->createSubscriber();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $subscriber);

        $middleware = new EnsureUserHasPermission();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'manage roles');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_ensure_user_has_any_role_middleware_allows_any_matching_role()
    {
        $editor = $this->createEditor();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $editor);

        $middleware = new EnsureUserHasAnyRole();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'Admin', 'Editor', 'Author');

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_ensure_user_has_any_role_middleware_denies_no_matching_roles()
    {
        $subscriber = $this->createSubscriber();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $subscriber);

        $middleware = new EnsureUserHasAnyRole();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'Admin', 'Editor', 'Author');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_ensure_user_has_any_permission_middleware_allows_any_matching_permission()
    {
        $author = $this->createAuthor();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $author);

        $middleware = new EnsureUserHasAnyPermission();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'manage roles', 'create pages', 'edit users');

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_ensure_user_has_any_permission_middleware_denies_no_matching_permissions()
    {
        $subscriber = $this->createSubscriber();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $subscriber);

        $middleware = new EnsureUserHasAnyPermission();
        $response = $middleware->handle($request, fn() => new Response('OK'), 'manage roles', 'create pages', 'edit users');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_middleware_works_with_super_admin_role()
    {
        $superAdmin = $this->createSuperAdmin();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $superAdmin);

        // Super Admin should pass all role checks
        $roleMiddleware = new EnsureUserHasRole();
        $response = $roleMiddleware->handle($request, fn() => new Response('OK'), 'Admin');
        $this->assertEquals('OK', $response->getContent());

        // Super Admin should pass all permission checks
        $permissionMiddleware = new EnsureUserHasPermission();
        $response = $permissionMiddleware->handle($request, fn() => new Response('OK'), 'manage roles');
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_works_with_multiple_roles()
    {
        $user = User::factory()->create();
        $user->assignRole(['Author', 'Editor']);
        
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $user);

        // Should pass with either role
        $middleware = new EnsureUserHasRole();
        
        $response = $middleware->handle($request, fn() => new Response('OK'), 'Author');
        $this->assertEquals('OK', $response->getContent());

        $response = $middleware->handle($request, fn() => new Response('OK'), 'Editor');
        $this->assertEquals('OK', $response->getContent());

        // Should fail with unassigned role
        $response = $middleware->handle($request, fn() => new Response('OK'), 'Admin');
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_middleware_works_with_direct_permissions()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view dashboard');
        
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsureUserHasPermission();
        
        // Should pass with directly assigned permission
        $response = $middleware->handle($request, fn() => new Response('OK'), 'view dashboard');
        $this->assertEquals('OK', $response->getContent());

        // Should fail with unassigned permission
        $response = $middleware->handle($request, fn() => new Response('OK'), 'manage roles');
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_middleware_handles_permission_inheritance()
    {
        $editor = $this->createEditor();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $editor);

        $middleware = new EnsureUserHasPermission();
        
        // Editor should have permissions from their role
        $editorPermissions = [
            'view dashboard',
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
        ];

        foreach ($editorPermissions as $permission) {
            $response = $middleware->handle($request, fn() => new Response('OK'), $permission);
            $this->assertEquals('OK', $response->getContent(), "Editor should have permission: {$permission}");
        }

        // Editor should not have admin permissions
        $adminPermissions = [
            'view users',
            'manage roles',
            'manage settings',
        ];

        foreach ($adminPermissions as $permission) {
            $response = $middleware->handle($request, fn() => new Response('OK'), $permission);
            $this->assertEquals(403, $response->getStatusCode(), "Editor should not have permission: {$permission}");
        }
    }

    public function test_middleware_error_messages_are_appropriate()
    {
        $subscriber = $this->createSubscriber();
        $request = Request::create('/test');
        $request->setUserResolver(fn() => $subscriber);

        // Test role middleware error
        $roleMiddleware = new EnsureUserHasRole();
        $response = $roleMiddleware->handle($request, fn() => new Response('OK'), 'Admin');
        $this->assertEquals(403, $response->getStatusCode());

        // Test permission middleware error
        $permissionMiddleware = new EnsureUserHasPermission();
        $response = $permissionMiddleware->handle($request, fn() => new Response('OK'), 'manage roles');
        $this->assertEquals(403, $response->getStatusCode());
    }
}

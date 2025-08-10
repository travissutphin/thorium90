<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\WithRoles;

/**
 * AdminSettingsTest
 * 
 * This test class covers the Admin Settings functionality for the Multi-Role User
 * Authentication system. It tests all aspects of settings management including
 * CRUD operations, permissions, validation, caching, and security.
 * 
 * Test Coverage:
 * - Settings dashboard access and permissions
 * - Settings CRUD operations (Create, Read, Update, Delete)
 * - Category-based settings organization
 * - Type-safe value handling and validation
 * - Permission-based access control
 * - Settings caching and performance
 * - Import/export functionality
 * - System statistics and monitoring
 * - Security settings protection
 * - Audit logging for changes
 * 
 * Following established patterns from UserRoleManagementTest and other admin tests.
 * 
 * @see Tests\Feature\Admin\UserRoleManagementTest
 * @see App\Http\Controllers\Admin\AdminSettingsController
 * @see App\Models\Setting
 */
class AdminSettingsTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
        
        // Create some test settings
        $this->createTestSettings();
    }

    /**
     * Create test settings for use in tests
     */
    protected function createTestSettings(): void
    {
        Setting::set('app.name', 'Test Application', 'string', 'application', 'Test app name', true);
        Setting::set('app.debug', false, 'boolean', 'application', 'Debug mode', false);
        Setting::set('auth.login_attempts', 5, 'integer', 'authentication', 'Login attempts limit', false);
        Setting::set('security.password_min_length', 8, 'integer', 'security', 'Minimum password length', true);
        Setting::set('features.api_enabled', true, 'boolean', 'features', 'Enable API', false);
        Setting::set('system.cache_duration', 3600, 'integer', 'system', 'Cache duration in seconds', false);
    }

    public function test_admin_can_view_settings_dashboard()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/settings/index')
                ->has('settings')
                ->has('categories')
                ->has('stats')
        );
    }

    public function test_user_without_manage_settings_permission_cannot_access_dashboard()
    {
        $author = $this->createAuthor();

        $response = $this->actingAs($author)->get('/admin/settings');

        $response->assertStatus(403);
    }

    public function test_settings_are_grouped_by_category()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('settings.application')
                ->has('settings.authentication')
                ->has('settings.security')
                ->has('settings.features')
                ->has('settings.system')
        );
    }

    public function test_admin_can_update_settings()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->put('/admin/settings', [
                'settings' => [
                    'app.name' => [
                        'value' => 'Updated Application Name',
                        'type' => 'string',
                        'category' => 'application',
                        'description' => 'Test app name',
                        'is_public' => true
                    ],
                    'app.debug' => [
                        'value' => true,
                        'type' => 'boolean',
                        'category' => 'application',
                        'description' => 'Debug mode',
                        'is_public' => false
                    ]
                ]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Updated Application Name', Setting::get('app.name'));
        $this->assertTrue(Setting::get('app.debug'));
    }

    public function test_admin_can_update_single_setting()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->put('/admin/settings/app.name', [
                'value' => 'Single Update Test',
                'type' => 'string'
            ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Setting updated successfully'
        ]);

        $this->assertEquals('Single Update Test', Setting::get('app.name'));
    }

    public function test_settings_validation_works_correctly()
    {
        $admin = $this->createAdmin();

        // Test invalid integer
        $response = $this->actingAs($admin)
            ->put('/admin/settings/auth.login_attempts', [
                'value' => 'not-a-number',
                'type' => 'integer'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['value']);
    }

    public function test_boolean_settings_are_handled_correctly()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->put('/admin/settings', [
                'settings' => [
                    'features.api_enabled' => [
                        'value' => false,
                        'type' => 'boolean',
                        'category' => 'features',
                        'description' => 'Enable API',
                        'is_public' => false
                    ]
                ]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertFalse(Setting::get('features.api_enabled'));
    }

    public function test_array_settings_are_handled_correctly()
    {
        $admin = $this->createAdmin();

        $testArray = ['value1', 'value2', 'value3'];

        $response = $this->actingAs($admin)
            ->put('/admin/settings', [
                'settings' => [
                    'test.array_setting' => [
                        'value' => $testArray,
                        'type' => 'array',
                        'category' => 'system',
                        'description' => 'Test array setting',
                        'is_public' => false
                    ]
                ]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals($testArray, Setting::get('test.array_setting'));
    }

    public function test_json_settings_are_handled_correctly()
    {
        $admin = $this->createAdmin();

        $testJson = ['key1' => 'value1', 'key2' => 'value2'];

        $response = $this->actingAs($admin)
            ->put('/admin/settings', [
                'settings' => [
                    'test.json_setting' => [
                        'value' => $testJson,
                        'type' => 'json',
                        'category' => 'system',
                        'description' => 'Test JSON setting',
                        'is_public' => false
                    ]
                ]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals($testJson, Setting::get('test.json_setting'));
    }

    public function test_security_settings_require_special_permission()
    {
        $admin = $this->createAdmin();

        // Admin doesn't have 'manage security settings' permission by default
        $response = $this->actingAs($admin)
            ->put('/admin/settings/security.password_min_length', [
                'value' => 12,
                'type' => 'integer'
            ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Insufficient permissions for security settings']);
    }

    public function test_super_admin_can_manage_security_settings()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->put('/admin/settings/security.password_min_length', [
                'value' => 12,
                'type' => 'integer'
            ]);

        $response->assertOk();
        $this->assertEquals(12, Setting::get('security.password_min_length'));
    }

    public function test_admin_can_reset_category_settings()
    {
        $admin = $this->createAdmin();

        // First, modify a setting
        Setting::set('app.name', 'Modified Name', 'string', 'application');

        $response = $this->actingAs($admin)
            ->post('/admin/settings/reset', [
                'category' => 'application'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Setting should be reset to default
        $this->assertEquals('Thorium90 Authentication System', Setting::get('app.name'));
    }

    public function test_only_super_admin_can_reset_all_settings()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post('/admin/settings/reset');

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only Super Admins can reset all settings.');
    }

    public function test_super_admin_can_reset_all_settings()
    {
        $superAdmin = $this->createSuperAdmin();

        // Modify some settings
        Setting::set('app.name', 'Modified Name', 'string', 'application');
        Setting::set('app.debug', true, 'boolean', 'application');

        $response = $this->actingAs($superAdmin)
            ->post('/admin/settings/reset');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Settings should be reset to defaults
        $this->assertEquals('Thorium90 Authentication System', Setting::get('app.name'));
        $this->assertFalse(Setting::get('app.debug'));
    }

    public function test_settings_are_cached_for_performance()
    {
        // Clear cache first
        Cache::flush();

        // First access should hit database
        $value = Setting::get('app.name');
        $this->assertEquals('Test Application', $value);

        // Modify setting directly in database (bypassing model)
        \DB::table('settings')->where('key', 'app.name')->update(['value' => 'Direct DB Update']);

        // Second access should return cached value
        $cachedValue = Setting::get('app.name');
        $this->assertEquals('Test Application', $cachedValue); // Still cached

        // Clear cache and access again
        Cache::flush();
        $freshValue = Setting::get('app.name');
        $this->assertEquals('Direct DB Update', $freshValue); // Now from DB
    }

    public function test_cache_is_cleared_when_settings_are_updated()
    {
        $admin = $this->createAdmin();

        // Access setting to cache it
        $originalValue = Setting::get('app.name');
        $this->assertEquals('Test Application', $originalValue);

        // Update setting via controller
        $this->actingAs($admin)
            ->put('/admin/settings/app.name', [
                'value' => 'Cache Test Update',
                'type' => 'string'
            ]);

        // Should get updated value (cache cleared)
        $updatedValue = Setting::get('app.name');
        $this->assertEquals('Cache Test Update', $updatedValue);
    }

    public function test_admin_can_export_settings()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/settings/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertHeader('Content-Disposition');

        $exportData = $response->json();
        $this->assertArrayHasKey('exported_at', $exportData);
        $this->assertArrayHasKey('exported_by', $exportData);
        $this->assertArrayHasKey('settings', $exportData);
        $this->assertEquals($admin->email, $exportData['exported_by']);
    }

    public function test_admin_can_import_settings()
    {
        $admin = $this->createAdmin();

        $importData = [
            'settings' => [
                [
                    'key' => 'test.imported_setting',
                    'value' => 'Imported Value',
                    'type' => 'string',
                    'category' => 'system',
                    'description' => 'Imported setting',
                    'is_public' => false
                ]
            ]
        ];

        // Create a temporary file
        $tempFile = tmpfile();
        fwrite($tempFile, json_encode($importData));
        $tempPath = stream_get_meta_data($tempFile)['uri'];

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'settings.json',
            'application/json',
            null,
            true
        );

        $response = $this->actingAs($admin)
            ->post('/admin/settings/import', [
                'file' => $uploadedFile
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Imported Value', Setting::get('test.imported_setting'));

        fclose($tempFile);
    }

    public function test_import_validates_file_format()
    {
        $admin = $this->createAdmin();

        $invalidData = "invalid json content";

        $tempFile = tmpfile();
        fwrite($tempFile, $invalidData);
        $tempPath = stream_get_meta_data($tempFile)['uri'];

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'invalid.json',
            'application/json',
            null,
            true
        );

        $response = $this->actingAs($admin)
            ->post('/admin/settings/import', [
                'file' => $uploadedFile
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        fclose($tempFile);
    }

    public function test_system_stats_are_accessible_with_permission()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/settings/stats');

        $response->assertOk();
        $response->assertJsonStructure([
            'users' => [
                'total',
                'active',
                'deleted',
                'by_role'
            ],
            'settings' => [
                'total',
                'by_category',
                'public'
            ],
            'system' => [
                'php_version',
                'laravel_version',
                'cache_enabled',
                'debug_mode',
                'maintenance_mode'
            ]
        ]);
    }

    public function test_user_without_view_system_stats_permission_cannot_access_stats()
    {
        $author = $this->createAuthor();

        $response = $this->actingAs($author)->get('/admin/settings/stats');

        $response->assertStatus(403);
    }

    public function test_settings_by_category_endpoint_works()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/settings/category/application');

        $response->assertOk();
        $response->assertJsonStructure([
            'settings',
            'category' => [
                'name',
                'description',
                'icon'
            ]
        ]);
    }

    public function test_default_role_validation_works()
    {
        $admin = $this->createAdmin();

        // Test valid role
        $response = $this->actingAs($admin)
            ->put('/admin/settings', [
                'settings' => [
                    'auth.default_role' => [
                        'value' => 'Editor',
                        'type' => 'string',
                        'category' => 'authentication',
                        'description' => 'Default role',
                        'is_public' => false
                    ]
                ]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Test invalid role
        $response = $this->actingAs($admin)
            ->put('/admin/settings', [
                'settings' => [
                    'auth.default_role' => [
                        'value' => 'NonexistentRole',
                        'type' => 'string',
                        'category' => 'authentication',
                        'description' => 'Default role',
                        'is_public' => false
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_email_validation_works_for_email_settings()
    {
        $admin = $this->createAdmin();

        // Test invalid email
        $response = $this->actingAs($admin)
            ->put('/admin/settings', [
                'settings' => [
                    'email.from_address' => [
                        'value' => 'invalid-email',
                        'type' => 'string',
                        'category' => 'email',
                        'description' => 'From email address',
                        'is_public' => false
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();

        // Test valid email
        $response = $this->actingAs($admin)
            ->put('/admin/settings', [
                'settings' => [
                    'email.from_address' => [
                        'value' => 'test@example.com',
                        'type' => 'string',
                        'category' => 'email',
                        'description' => 'From email address',
                        'is_public' => false
                    ]
                ]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_settings_model_static_methods_work_correctly()
    {
        // Test set and get
        Setting::set('test.static_method', 'test value', 'string', 'test');
        $this->assertEquals('test value', Setting::get('test.static_method'));

        // Test has
        $this->assertTrue(Setting::has('test.static_method'));
        $this->assertFalse(Setting::has('nonexistent.setting'));

        // Test forget
        $this->assertTrue(Setting::forget('test.static_method'));
        $this->assertFalse(Setting::has('test.static_method'));

        // Test default value
        $this->assertEquals('default', Setting::get('nonexistent.setting', 'default'));
    }

    public function test_settings_by_category_method_works()
    {
        $applicationSettings = Setting::getByCategory('application');
        
        $this->assertArrayHasKey('app.name', $applicationSettings);
        $this->assertArrayHasKey('app.debug', $applicationSettings);
        $this->assertEquals('Test Application', $applicationSettings['app.name']);
    }

    public function test_public_settings_filtering_works()
    {
        $publicSettings = Setting::getAll(true);
        $allSettings = Setting::getAll(false);

        $this->assertArrayHasKey('app.name', $publicSettings); // is_public = true
        $this->assertArrayNotHasKey('app.debug', $publicSettings); // is_public = false

        $this->assertArrayHasKey('app.name', $allSettings);
        $this->assertArrayHasKey('app.debug', $allSettings);
    }

    public function test_settings_type_casting_works_correctly()
    {
        // Test boolean casting
        Setting::set('test.boolean_true', '1', 'boolean', 'test');
        Setting::set('test.boolean_false', '0', 'boolean', 'test');
        
        $this->assertTrue(Setting::get('test.boolean_true'));
        $this->assertFalse(Setting::get('test.boolean_false'));

        // Test integer casting
        Setting::set('test.integer', '123', 'integer', 'test');
        $this->assertSame(123, Setting::get('test.integer'));

        // Test JSON casting
        $jsonData = ['key' => 'value', 'number' => 42];
        Setting::set('test.json', $jsonData, 'json', 'test');
        $this->assertEquals($jsonData, Setting::get('test.json'));
    }

    public function test_grouped_by_category_method_works()
    {
        $grouped = Setting::getGroupedByCategory();

        $this->assertArrayHasKey('application', $grouped);
        $this->assertArrayHasKey('authentication', $grouped);
        $this->assertArrayHasKey('security', $grouped);

        $this->assertArrayHasKey('app.name', $grouped['application']);
        $this->assertArrayHasKey('value', $grouped['application']['app.name']);
        $this->assertArrayHasKey('type', $grouped['application']['app.name']);
        $this->assertArrayHasKey('description', $grouped['application']['app.name']);
    }
}

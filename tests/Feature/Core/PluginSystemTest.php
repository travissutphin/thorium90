<?php

namespace Tests\Feature\Core;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithRoles;
use App\Core\Plugin\PluginManager;
use App\Core\Plugin\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class PluginSystemTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected PluginManager $pluginManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
        ]);

        $this->pluginManager = app(PluginManager::class);
        
        // Clear plugin cache before each test
        $this->pluginManager->clearCache();
    }

    /** @test */
    public function it_can_discover_plugins()
    {
        $this->pluginManager->discoverPlugins();
        
        $plugins = $this->pluginManager->getAllPlugins();
        
        // Should find the sample blog plugin
        $this->assertTrue($plugins->has('sample-blog'));
        
        $samplePlugin = $plugins->get('sample-blog');
        $this->assertInstanceOf(Plugin::class, $samplePlugin);
        $this->assertEquals('Sample Blog Plugin', $samplePlugin->getName());
        $this->assertEquals('1.0.0', $samplePlugin->getVersion());
    }

    /** @test */
    public function it_can_get_plugin_statistics()
    {
        $this->pluginManager->discoverPlugins();
        
        $stats = $this->pluginManager->getStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('active', $stats);
        $this->assertArrayHasKey('inactive', $stats);
        $this->assertArrayHasKey('by_category', $stats);
        
        $this->assertGreaterThanOrEqual(1, $stats['total']);
    }

    /** @test */
    public function it_can_enable_and_disable_plugins()
    {
        $this->pluginManager->discoverPlugins();
        
        $pluginId = 'sample-blog';
        
        // Initially should not be enabled
        $this->assertFalse($this->pluginManager->isPluginEnabled($pluginId));
        
        // Enable plugin
        $result = $this->pluginManager->enablePlugin($pluginId);
        $this->assertTrue($result);
        $this->assertTrue($this->pluginManager->isPluginEnabled($pluginId));
        
        // Disable plugin
        $result = $this->pluginManager->disablePlugin($pluginId);
        $this->assertTrue($result);
        $this->assertFalse($this->pluginManager->isPluginEnabled($pluginId));
    }

    /** @test */
    public function it_can_get_plugin_templates_and_blocks()
    {
        $this->pluginManager->discoverPlugins();
        
        $plugin = $this->pluginManager->getPlugin('sample-blog');
        $this->assertNotNull($plugin);
        
        $templates = $plugin->getTemplates();
        $this->assertIsArray($templates);
        $this->assertCount(2, $templates);
        
        $blocks = $plugin->getBlocks();
        $this->assertIsArray($blocks);
        $this->assertCount(3, $blocks);
        
        $layouts = $plugin->getLayouts();
        $this->assertIsArray($layouts);
        $this->assertCount(1, $layouts);
        
        $themes = $plugin->getThemes();
        $this->assertIsArray($themes);
        $this->assertCount(1, $themes);
    }

    /** @test */
    public function admin_can_view_plugins_page()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $response = $this->actingAs($admin)
                         ->get(route('admin.plugins.index'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/plugins/index')
                 ->has('plugins')
                 ->has('stats')
                 ->has('categories')
        );
    }

    /** @test */
    public function admin_can_enable_plugin()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $this->pluginManager->discoverPlugins();
        
        $response = $this->actingAs($admin)
                         ->post(route('admin.plugins.enable', 'sample-blog'));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertTrue($this->pluginManager->isPluginEnabled('sample-blog'));
    }

    /** @test */
    public function admin_can_disable_plugin()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $this->pluginManager->discoverPlugins();
        $this->pluginManager->enablePlugin('sample-blog');
        
        $response = $this->actingAs($admin)
                         ->post(route('admin.plugins.disable', 'sample-blog'));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertFalse($this->pluginManager->isPluginEnabled('sample-blog'));
    }

    /** @test */
    public function admin_can_view_plugin_details()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $this->pluginManager->discoverPlugins();
        
        $response = $this->actingAs($admin)
                         ->get(route('admin.plugins.show', 'sample-blog'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('admin/plugins/show')
                 ->has('plugin')
                 ->where('plugin.id', 'sample-blog')
                 ->where('plugin.name', 'Sample Blog Plugin')
        );
    }

    /** @test */
    public function admin_can_perform_bulk_actions()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $this->pluginManager->discoverPlugins();
        
        $response = $this->actingAs($admin)
                         ->post(route('admin.plugins.bulk-action'), [
                             'action' => 'enable',
                             'plugin_ids' => ['sample-blog']
                         ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertTrue($this->pluginManager->isPluginEnabled('sample-blog'));
    }

    /** @test */
    public function admin_can_clear_plugin_cache()
    {
        $admin = $this->createUserWithRole('Admin');
        
        // Set some cache data
        Cache::put('enabled_plugins', ['sample-blog']);
        
        $response = $this->actingAs($admin)
                         ->post(route('admin.plugins.clear-cache'));
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Cache should be cleared
        $this->assertEquals([], Cache::get('enabled_plugins', []));
    }

    /** @test */
    public function admin_can_get_plugin_stats_api()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $this->pluginManager->discoverPlugins();
        
        $response = $this->actingAs($admin)
                         ->get(route('admin.plugins.stats'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total',
            'active',
            'inactive',
            'by_category'
        ]);
    }

    /** @test */
    public function admin_can_export_plugin_list()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $this->pluginManager->discoverPlugins();
        
        $response = $this->actingAs($admin)
                         ->get(route('admin.plugins.export'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    /** @test */
    public function non_admin_cannot_access_plugin_management()
    {
        $user = $this->createUserWithRole('User');
        
        $response = $this->actingAs($user)
                         ->get(route('admin.plugins.index'));
        
        $response->assertStatus(403);
    }

    /** @test */
    public function plugin_validation_works_correctly()
    {
        // Test with invalid manifest
        $invalidManifest = [
            'name' => 'Test Plugin',
            // Missing required fields
        ];
        
        $reflection = new \ReflectionClass($this->pluginManager);
        $method = $reflection->getMethod('validateManifest');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->pluginManager, $invalidManifest);
        $this->assertFalse($result);
        
        // Test with valid manifest
        $validManifest = [
            'id' => 'test-plugin',
            'name' => 'Test Plugin',
            'version' => '1.0.0',
            'description' => 'A test plugin',
            'author' => 'Test Author'
        ];
        
        $result = $method->invoke($this->pluginManager, $validManifest);
        $this->assertTrue($result);
    }

    /** @test */
    public function plugin_can_be_converted_to_array()
    {
        $this->pluginManager->discoverPlugins();
        
        $plugin = $this->pluginManager->getPlugin('sample-blog');
        $this->assertNotNull($plugin);
        
        $array = $plugin->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('version', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('author', $array);
        $this->assertArrayHasKey('category', $array);
        $this->assertArrayHasKey('path', $array);
        $this->assertArrayHasKey('booted', $array);
        $this->assertArrayHasKey('manifest', $array);
        
        $this->assertEquals('sample-blog', $array['id']);
        $this->assertEquals('Sample Blog Plugin', $array['name']);
    }
}

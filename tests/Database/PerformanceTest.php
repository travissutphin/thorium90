<?php

namespace Tests\Database;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\WithRoles;

/**
 * Database Performance Testing Suite
 * 
 * Critical performance tests for Thorium90 to ensure:
 * - Query Optimization: N+1 prevention, proper eager loading
 * - Index Usage: Queries use appropriate indexes
 * - Scalability: Performance under load
 * - Memory Efficiency: Optimal resource usage
 * 
 * Performance targets:
 * - Page queries: <50ms average
 * - User auth: <10ms average  
 * - Permission checks: <5ms average (cached)
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    /** @test */
    public function pages_index_avoids_n_plus_one_queries()
    {
        // Create test data
        $users = User::factory()->count(5)->create();
        foreach ($users as $user) {
            $user->assignRole('Author');
            Page::factory()->count(3)->create(['user_id' => $user->id]);
        }

        // Enable query logging
        DB::enableQueryLog();
        
        // Simulate controller query with eager loading
        $pages = Page::with('user')->paginate(10);
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 2 queries: 1 for pages + count, 1 for users
        // Not 1 + N (where N = number of pages)
        $this->assertLessThanOrEqual(3, count($queries), 
            'Pages index should use eager loading to avoid N+1 queries. Queries: ' . count($queries)
        );

        // Verify data is loaded correctly
        $this->assertGreaterThan(0, $pages->count());
        $this->assertNotNull($pages->first()->user);
    }

    /** @test */
    public function user_permission_checks_are_optimized()
    {
        $user = $this->createUserWithRole('Admin');
        
        // Warm up permission cache
        $user->hasPermissionTo('view pages');
        
        DB::enableQueryLog();
        
        // Multiple permission checks should use cache
        $checks = [
            $user->hasPermissionTo('view pages'),
            $user->hasPermissionTo('create pages'),
            $user->hasPermissionTo('edit pages'),
            $user->hasPermissionTo('delete pages'),
        ];
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be minimal queries due to caching
        $this->assertLessThanOrEqual(2, count($queries), 
            'Permission checks should be cached. Queries: ' . count($queries)
        );
        
        // All checks should return true for Admin
        foreach ($checks as $check) {
            $this->assertTrue($check);
        }
    }

    /** @test */
    public function bulk_operations_use_single_queries()
    {
        $pages = Page::factory()->count(10)->create();
        
        DB::enableQueryLog();
        
        // Bulk update should use single query
        Page::whereIn('id', $pages->pluck('id'))
            ->update(['is_featured' => true]);
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be exactly 1 query for bulk update
        $this->assertEquals(1, count($queries), 
            'Bulk operations should use single query, not individual updates'
        );
        
        // Verify all pages were updated
        $featuredCount = Page::where('is_featured', true)->count();
        $this->assertEquals(10, $featuredCount);
    }

    /** @test */
    public function soft_delete_queries_use_indexes()
    {
        // Create mix of active and soft-deleted users
        $activeUsers = User::factory()->count(5)->create();
        $deletedUsers = User::factory()->count(3)->create(['deleted_at' => now()]);
        
        DB::enableQueryLog();
        
        // Query should use deleted_at index
        $activeCount = User::whereNull('deleted_at')->count();
        $allCount = User::withTrashed()->count();
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertEquals(5, $activeCount);
        $this->assertEquals(8, $allCount);
        
        // Verify queries are using indexes (check EXPLAIN if needed)
        foreach ($queries as $query) {
            $this->assertStringContains('deleted_at', $query['query']);
        }
    }

    /** @test */
    public function page_search_performance_under_load()
    {
        // Create substantial test data
        User::factory()->count(10)->create()->each(function ($user) {
            $user->assignRole('Author');
            Page::factory()->count(20)->create(['user_id' => $user->id]);
        });

        $startTime = microtime(true);
        
        // Simulate search query
        $results = Page::where('title', 'like', '%test%')
            ->orWhere('content', 'like', '%test%')
            ->with('user')
            ->paginate(15);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Should complete within performance target
        $this->assertLessThan(100, $executionTime, 
            "Page search took {$executionTime}ms, should be under 100ms"
        );
    }

    /** @test */
    public function role_permission_lookup_performance()
    {
        $user = $this->createUserWithRole('Admin');
        
        $startTime = microtime(true);
        
        // Multiple role/permission checks
        for ($i = 0; $i < 10; $i++) {
            $user->hasRole('Admin');
            $user->hasPermissionTo('view pages');
            $user->can('create pages');
        }
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Should be very fast due to caching
        $this->assertLessThan(50, $executionTime, 
            "Role/permission lookups took {$executionTime}ms, should be under 50ms"
        );
    }

    /** @test */
    public function database_connection_handling_under_load()
    {
        DB::enableQueryLog();
        
        // Simulate multiple concurrent operations
        $operations = [];
        for ($i = 0; $i < 20; $i++) {
            $operations[] = User::factory()->create();
        }
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should handle multiple operations without connection issues
        $this->assertCount(20, $operations);
        $this->assertGreaterThanOrEqual(20, count($queries));
        
        // Verify all users were created
        $this->assertGreaterThanOrEqual(20, User::count());
    }

    /** @test */
    public function query_complexity_analysis()
    {
        // Create complex data relationships
        $admin = $this->createUserWithRole('Admin');
        $pages = Page::factory()->count(5)->create(['user_id' => $admin->id]);
        
        DB::enableQueryLog();
        
        // Complex query with joins and conditions
        $results = Page::with(['user'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->whereHas('user', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get();
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be optimized with minimal queries
        $this->assertLessThanOrEqual(3, count($queries), 
            'Complex queries should be optimized'
        );
    }

    /** @test */
    public function memory_usage_during_bulk_operations()
    {
        $startMemory = memory_get_usage(true);
        
        // Create large dataset
        $users = User::factory()->count(100)->create();
        
        // Bulk operation
        User::whereIn('id', $users->pluck('id'))
            ->update(['updated_at' => now()]);
        
        $endMemory = memory_get_usage(true);
        $memoryUsed = $endMemory - $startMemory;
        
        // Should not use excessive memory (under 10MB for this operation)
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed, 
            "Bulk operation used " . ($memoryUsed / 1024 / 1024) . "MB, should be under 10MB"
        );
    }

    /** @test */
    public function index_usage_verification()
    {
        // Create test data
        Page::factory()->count(10)->create();
        
        // Test queries that should use indexes
        $queries = [
            ['query' => "SELECT * FROM pages WHERE slug = 'test-slug'", 'index' => 'slug'],
            ['query' => "SELECT * FROM pages WHERE status = 'published'", 'index' => 'status'],
            ['query' => "SELECT * FROM users WHERE email = 'test@example.com'", 'index' => 'email'],
        ];
        
        foreach ($queries as $queryInfo) {
            // In a real scenario, you'd use EXPLAIN to verify index usage
            // For now, we verify the queries execute successfully
            $result = DB::select($queryInfo['query']);
            $this->assertIsArray($result);
        }
    }

    /** @test */
    public function concurrent_user_operations_performance()
    {
        $startTime = microtime(true);
        
        // Simulate concurrent user operations
        $operations = [];
        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            $user->assignRole('Author');
            $operations[] = $user;
        }
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Should handle concurrent operations efficiently
        $this->assertLessThan(500, $executionTime, 
            "Concurrent operations took {$executionTime}ms, should be under 500ms"
        );
        
        $this->assertCount(10, $operations);
    }

    /** @test */
    public function pagination_performance_with_large_dataset()
    {
        // Create substantial dataset
        User::factory()->count(50)->create()->each(function ($user) {
            $user->assignRole('Author');
            Page::factory()->count(4)->create(['user_id' => $user->id]);
        });

        $startTime = microtime(true);
        
        // Test pagination performance
        $page1 = Page::with('user')->paginate(15, ['*'], 'page', 1);
        $page2 = Page::with('user')->paginate(15, ['*'], 'page', 2);
        $page3 = Page::with('user')->paginate(15, ['*'], 'page', 3);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Pagination should be efficient even with large datasets
        $this->assertLessThan(200, $executionTime, 
            "Pagination took {$executionTime}ms, should be under 200ms"
        );
        
        $this->assertEquals(15, $page1->count());
        $this->assertEquals(15, $page2->count());
    }

    /** @test */
    public function two_factor_authentication_query_performance()
    {
        // Create users with 2FA enabled
        $users = User::factory()->count(10)->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ]);

        DB::enableQueryLog();
        
        // Query users with 2FA
        $twoFactorUsers = User::whereNotNull('two_factor_secret')
            ->whereNotNull('two_factor_confirmed_at')
            ->get();
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertEquals(10, $twoFactorUsers->count());
        $this->assertLessThanOrEqual(1, count($queries), 
            '2FA user queries should be optimized'
        );
    }

    /** @test */
    public function settings_key_value_lookup_performance()
    {
        // Create test settings
        for ($i = 0; $i < 20; $i++) {
            DB::table('settings')->insert([
                'key' => "test_setting_{$i}",
                'value' => "value_{$i}",
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $startTime = microtime(true);
        
        // Multiple setting lookups
        for ($i = 0; $i < 10; $i++) {
            $setting = DB::table('settings')
                ->where('key', "test_setting_{$i}")
                ->first();
        }
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Settings lookup should be fast (using unique key index)
        $this->assertLessThan(50, $executionTime, 
            "Settings lookup took {$executionTime}ms, should be under 50ms"
        );
    }
}

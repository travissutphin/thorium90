<?php

namespace Tests\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migration Testing Suite
 * 
 * Critical tests for Thorium90's database migrations to ensure:
 * - Stability: All migrations run successfully
 * - Security: Proper constraints and relationships
 * - Scalability: Optimal indexes and structure
 * 
 * This test suite validates the core database infrastructure
 * that supports the multi-role authentication system.
 */
class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function all_migrations_run_successfully_from_scratch()
    {
        // Fresh database - migrations should run without errors
        $this->artisan('migrate:fresh')
            ->assertExitCode(0);

        // Verify all expected tables exist
        $expectedTables = [
            'users',
            'cache',
            'jobs',
            'roles',
            'permissions',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'personal_access_tokens',
            'settings',
            'pages',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' should exist after migrations"
            );
        }
    }

    /** @test */
    public function migrations_are_reversible()
    {
        // Run all migrations
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Get list of migrations that were run
        $migrations = DB::table('migrations')->pluck('migration')->toArray();
        
        // Rollback all migrations
        $this->artisan('migrate:reset')->assertExitCode(0);
        
        // Verify all tables are gone (except migrations table)
        $remainingTables = DB::select("SHOW TABLES");
        $tableNames = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $remainingTables);
        
        // Only migrations table should remain
        $this->assertContains('migrations', $tableNames);
        $this->assertCount(1, $tableNames, 'Only migrations table should remain after rollback');
    }

    /** @test */
    public function migration_rollback_preserves_existing_data()
    {
        // Run migrations and seed some test data
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Create test data
        DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $userId = DB::table('users')->where('email', 'test@example.com')->value('id');
        $this->assertNotNull($userId);
        
        // Rollback the last migration (pages table)
        $this->artisan('migrate:rollback', ['--step' => 1])->assertExitCode(0);
        
        // Verify user data is still there
        $user = DB::table('users')->where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->name);
        
        // Verify pages table is gone
        $this->assertFalse(Schema::hasTable('pages'));
    }

    /** @test */
    public function migrations_are_idempotent()
    {
        // Run migrations twice - should not cause errors
        $this->artisan('migrate:fresh')->assertExitCode(0);
        $this->artisan('migrate')->assertExitCode(0);
        
        // Verify tables still exist and are properly structured
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('pages'));
        $this->assertTrue(Schema::hasTable('roles'));
    }

    /** @test */
    public function foreign_key_constraints_are_enforced()
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Test pages -> users foreign key
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        DB::table('pages')->insert([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test content',
            'status' => 'draft',
            'user_id' => 99999, // Non-existent user
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function indexes_are_created_with_proper_names()
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Check critical indexes exist
        $indexes = $this->getTableIndexes('users');
        $this->assertContains('users_email_unique', $indexes, 'Users email unique index should exist');
        
        $pageIndexes = $this->getTableIndexes('pages');
        $this->assertContains('pages_slug_unique', $pageIndexes, 'Pages slug unique index should exist');
        $this->assertContains('pages_user_id_foreign', $pageIndexes, 'Pages user_id foreign key should exist');
    }

    /** @test */
    public function migration_order_dependencies_are_correct()
    {
        // Fresh migration should handle dependencies correctly
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Verify dependent tables are created after their dependencies
        // Users table should exist before pages table (foreign key dependency)
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('pages'));
        
        // Verify foreign key exists
        $foreignKeys = $this->getForeignKeys('pages');
        $userForeignKey = collect($foreignKeys)->firstWhere('COLUMN_NAME', 'user_id');
        $this->assertNotNull($userForeignKey, 'Pages table should have foreign key to users');
        $this->assertEquals('users', $userForeignKey->REFERENCED_TABLE_NAME);
    }

    /** @test */
    public function permission_tables_structure_is_correct()
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Verify Spatie permission tables exist with correct structure
        $permissionTables = [
            'permissions',
            'roles', 
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions'
        ];
        
        foreach ($permissionTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Permission table '{$table}' should exist");
        }
        
        // Verify permissions table structure
        $this->assertTrue(Schema::hasColumn('permissions', 'name'));
        $this->assertTrue(Schema::hasColumn('permissions', 'guard_name'));
        
        // Verify roles table structure
        $this->assertTrue(Schema::hasColumn('roles', 'name'));
        $this->assertTrue(Schema::hasColumn('roles', 'guard_name'));
    }

    /** @test */
    public function two_factor_columns_are_properly_structured()
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Verify 2FA columns exist in users table
        $this->assertTrue(Schema::hasColumn('users', 'two_factor_secret'));
        $this->assertTrue(Schema::hasColumn('users', 'two_factor_recovery_codes'));
        $this->assertTrue(Schema::hasColumn('users', 'two_factor_confirmed_at'));
        
        // Verify columns are nullable (optional feature)
        $columns = Schema::getColumnListing('users');
        $userTable = DB::select("DESCRIBE users");
        
        $twoFactorSecret = collect($userTable)->firstWhere('Field', 'two_factor_secret');
        $this->assertEquals('YES', $twoFactorSecret->Null, '2FA secret should be nullable');
        
        $recoveryCodes = collect($userTable)->firstWhere('Field', 'two_factor_recovery_codes');
        $this->assertEquals('YES', $recoveryCodes->Null, '2FA recovery codes should be nullable');
    }

    /** @test */
    public function soft_deletes_are_properly_implemented()
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Verify soft delete columns exist
        $this->assertTrue(Schema::hasColumn('users', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('pages', 'deleted_at'));
        
        // Verify deleted_at is nullable
        $userTable = DB::select("DESCRIBE users");
        $deletedAt = collect($userTable)->firstWhere('Field', 'deleted_at');
        $this->assertEquals('YES', $deletedAt->Null, 'deleted_at should be nullable');
    }

    /** @test */
    public function pages_table_has_correct_structure()
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Verify all required columns exist
        $requiredColumns = [
            'id', 'title', 'slug', 'content', 'excerpt', 'status',
            'is_featured', 'published_at', 'meta_title', 'meta_description',
            'meta_keywords', 'schema_type', 'schema_data', 'user_id',
            'created_at', 'updated_at', 'deleted_at'
        ];
        
        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('pages', $column),
                "Pages table should have '{$column}' column"
            );
        }
        
        // Verify slug is unique
        $indexes = $this->getTableIndexes('pages');
        $this->assertContains('pages_slug_unique', $indexes);
        
        // Verify status enum values
        $pageTable = DB::select("DESCRIBE pages");
        $statusColumn = collect($pageTable)->firstWhere('Field', 'status');
        $this->assertStringContains('draft', $statusColumn->Type);
        $this->assertStringContains('published', $statusColumn->Type);
        $this->assertStringContains('private', $statusColumn->Type);
    }

    /** @test */
    public function settings_table_structure_supports_key_value_storage()
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        
        // Verify settings table structure
        $this->assertTrue(Schema::hasColumn('settings', 'key'));
        $this->assertTrue(Schema::hasColumn('settings', 'value'));
        $this->assertTrue(Schema::hasColumn('settings', 'type'));
        
        // Verify key is unique
        $indexes = $this->getTableIndexes('settings');
        $this->assertContains('settings_key_unique', $indexes);
    }

    /**
     * Helper method to get table indexes
     */
    private function getTableIndexes(string $table): array
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        return array_map(function($index) {
            return $index->Key_name;
        }, $indexes);
    }

    /**
     * Helper method to get foreign keys for a table
     */
    private function getForeignKeys(string $table): array
    {
        return DB::select("
            SELECT 
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE 
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '{$table}'
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
    }
}

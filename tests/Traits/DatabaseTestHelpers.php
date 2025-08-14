<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database Test Helpers Trait
 * 
 * Common utilities for database testing in Thorium90.
 * Provides helper methods for:
 * - Database state verification
 * - Query analysis
 * - Performance measurement
 * - Data integrity checks
 */
trait DatabaseTestHelpers
{
    /**
     * Get all indexes for a given table
     */
    protected function getTableIndexes(string $table): array
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        return array_map(function($index) {
            return $index->Key_name;
        }, $indexes);
    }

    /**
     * Get foreign key constraints for a table
     */
    protected function getForeignKeys(string $table): array
    {
        return DB::select("
            SELECT 
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME,
                CONSTRAINT_NAME
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE 
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '{$table}'
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
    }

    /**
     * Verify table has expected columns
     */
    protected function assertTableHasColumns(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn($table, $column),
                "Table '{$table}' should have column '{$column}'"
            );
        }
    }

    /**
     * Verify table has expected indexes
     */
    protected function assertTableHasIndexes(string $table, array $expectedIndexes): void
    {
        $actualIndexes = $this->getTableIndexes($table);
        
        foreach ($expectedIndexes as $index) {
            $this->assertContains(
                $index,
                $actualIndexes,
                "Table '{$table}' should have index '{$index}'"
            );
        }
    }

    /**
     * Get column information for a table
     */
    protected function getColumnInfo(string $table): array
    {
        return DB::select("DESCRIBE {$table}");
    }

    /**
     * Verify column is nullable
     */
    protected function assertColumnIsNullable(string $table, string $column): void
    {
        $columns = $this->getColumnInfo($table);
        $columnInfo = collect($columns)->firstWhere('Field', $column);
        
        $this->assertNotNull($columnInfo, "Column '{$column}' should exist in table '{$table}'");
        $this->assertEquals('YES', $columnInfo->Null, "Column '{$column}' should be nullable");
    }

    /**
     * Verify column is not nullable
     */
    protected function assertColumnIsNotNullable(string $table, string $column): void
    {
        $columns = $this->getColumnInfo($table);
        $columnInfo = collect($columns)->firstWhere('Field', $column);
        
        $this->assertNotNull($columnInfo, "Column '{$column}' should exist in table '{$table}'");
        $this->assertEquals('NO', $columnInfo->Null, "Column '{$column}' should not be nullable");
    }

    /**
     * Verify enum column has expected values
     */
    protected function assertEnumColumnHasValues(string $table, string $column, array $expectedValues): void
    {
        $columns = $this->getColumnInfo($table);
        $columnInfo = collect($columns)->firstWhere('Field', $column);
        
        $this->assertNotNull($columnInfo, "Column '{$column}' should exist in table '{$table}'");
        
        foreach ($expectedValues as $value) {
            $this->assertStringContainsString(
                $value,
                $columnInfo->Type,
                "Enum column '{$column}' should contain value '{$value}'"
            );
        }
    }

    /**
     * Count queries executed during a callback
     */
    protected function countQueries(callable $callback): int
    {
        DB::enableQueryLog();
        $callback();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        return count($queries);
    }

    /**
     * Measure execution time of a callback
     */
    protected function measureExecutionTime(callable $callback): float
    {
        $startTime = microtime(true);
        $callback();
        $endTime = microtime(true);
        
        return ($endTime - $startTime) * 1000; // Convert to milliseconds
    }

    /**
     * Get memory usage during callback execution
     */
    protected function measureMemoryUsage(callable $callback): int
    {
        $startMemory = memory_get_usage(true);
        $callback();
        $endMemory = memory_get_usage(true);
        
        return $endMemory - $startMemory;
    }

    /**
     * Verify database transaction rollback works
     */
    protected function assertTransactionRollback(callable $callback): void
    {
        $initialState = $this->getDatabaseState();
        
        try {
            DB::transaction(function () use ($callback) {
                $callback();
                throw new \Exception('Force rollback');
            });
        } catch (\Exception $e) {
            // Expected exception
        }
        
        $finalState = $this->getDatabaseState();
        $this->assertEquals($initialState, $finalState, 'Database state should be unchanged after rollback');
    }

    /**
     * Get current database state (table row counts)
     */
    protected function getDatabaseState(): array
    {
        $tables = ['users', 'pages', 'roles', 'permissions', 'settings'];
        $state = [];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $state[$table] = DB::table($table)->count();
            }
        }
        
        return $state;
    }

    /**
     * Verify query uses specific index (MySQL specific)
     */
    protected function assertQueryUsesIndex(string $query, string $expectedIndex): void
    {
        $explain = DB::select("EXPLAIN {$query}");
        
        if (!empty($explain)) {
            $usedKey = $explain[0]->key ?? null;
            $this->assertEquals(
                $expectedIndex,
                $usedKey,
                "Query should use index '{$expectedIndex}' but used '{$usedKey}'"
            );
        }
    }

    /**
     * Create large dataset for performance testing
     */
    protected function createLargeDataset(string $model, int $count = 1000): void
    {
        $modelClass = "App\\Models\\{$model}";
        
        if (class_exists($modelClass)) {
            $modelClass::factory()->count($count)->create();
        }
    }

    /**
     * Verify soft delete behavior
     */
    protected function assertSoftDeleteBehavior($model): void
    {
        $originalId = $model->id;
        
        // Soft delete
        $model->delete();
        
        // Should be soft deleted
        $this->assertSoftDeleted($model->getTable(), ['id' => $originalId]);
        
        // Should not appear in normal queries
        $found = $model->newQuery()->find($originalId);
        $this->assertNull($found);
        
        // Should appear in trashed queries
        $trashed = $model->newQuery()->withTrashed()->find($originalId);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
    }

    /**
     * Verify unique constraint enforcement
     */
    protected function assertUniqueConstraint(string $table, string $column, $value): void
    {
        // Insert first record
        DB::table($table)->insert([
            $column => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Attempt to insert duplicate should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table($table)->insert([
            $column => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Verify foreign key constraint enforcement
     */
    protected function assertForeignKeyConstraint(string $table, string $column, $invalidValue): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        DB::table($table)->insert([
            $column => $invalidValue,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Clean up test data
     */
    protected function cleanupTestData(): void
    {
        $tables = ['pages', 'model_has_roles', 'model_has_permissions', 'users'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }

    /**
     * Verify data encryption
     */
    protected function assertDataIsEncrypted(string $table, string $column, $recordId, string $originalValue): void
    {
        $record = DB::table($table)->where('id', $recordId)->first();
        
        $this->assertNotNull($record, "Record should exist in table '{$table}'");
        $this->assertNotEquals(
            $originalValue,
            $record->{$column},
            "Column '{$column}' should be encrypted"
        );
        $this->assertStringNotContainsString(
            $originalValue,
            $record->{$column},
            "Encrypted column should not contain original value"
        );
    }

    /**
     * Verify JSON column structure
     */
    protected function assertJsonColumnStructure(string $table, string $column, $recordId, array $expectedKeys): void
    {
        $record = DB::table($table)->where('id', $recordId)->first();
        
        $this->assertNotNull($record, "Record should exist in table '{$table}'");
        
        $jsonData = json_decode($record->{$column}, true);
        $this->assertIsArray($jsonData, "Column '{$column}' should contain valid JSON");
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $jsonData,
                "JSON column '{$column}' should have key '{$key}'"
            );
        }
    }

    /**
     * Simulate concurrent database operations
     */
    protected function simulateConcurrentOperations(callable $operation, int $count = 5): array
    {
        $results = [];
        
        for ($i = 0; $i < $count; $i++) {
            try {
                $results[] = $operation();
            } catch (\Exception $e) {
                $results[] = $e;
            }
        }
        
        return $results;
    }

    /**
     * Verify database connection pool handling
     */
    protected function assertConnectionPoolHandling(): void
    {
        $connections = [];
        
        // Create multiple connections
        for ($i = 0; $i < 10; $i++) {
            $connections[] = DB::connection();
        }
        
        // All should be valid connections
        foreach ($connections as $connection) {
            $this->assertNotNull($connection);
            $this->assertTrue($connection->getPdo() instanceof \PDO);
        }
    }
}

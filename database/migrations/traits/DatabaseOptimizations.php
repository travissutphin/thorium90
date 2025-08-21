<?php

namespace Database\Migrations\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

trait DatabaseOptimizations
{
    /**
     * Apply database-specific optimizations to a table blueprint
     */
    protected function applyDatabaseOptimizations(Blueprint $table): void
    {
        $driver = DB::getDriverName();
        
        switch ($driver) {
            case 'mysql':
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                break;
                
            case 'pgsql':
                // PostgreSQL-specific optimizations could go here
                break;
                
            case 'sqlite':
                // SQLite-specific optimizations could go here
                break;
        }
    }
    
    /**
     * Execute database-specific statements for foreign key handling
     */
    protected function handleForeignKeys(callable $callback): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $callback();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else {
            $callback();
        }
    }
    
    /**
     * Get database-specific column definitions
     */
    protected function getJsonColumnType(): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql' => 'json',
            'pgsql' => 'jsonb', // Use JSONB for better performance in PostgreSQL
            'sqlite' => 'text', // SQLite stores JSON as text
            default => 'json'
        };
    }
    
    /**
     * Get database-specific text column type for large content
     */
    protected function getLongTextColumnType(): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql' => 'longtext',
            'pgsql' => 'text', // PostgreSQL text type can handle large content
            'sqlite' => 'text',
            default => 'text'
        };
    }
    
    /**
     * Add database-specific indexes for JSON columns
     */
    protected function addJsonIndexes(Blueprint $table, string $column, array $paths = []): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql' && version_compare(DB::select('SELECT VERSION() as version')[0]->version, '8.0', '>=')) {
            // MySQL 8.0+ supports functional indexes on JSON
            foreach ($paths as $path) {
                $indexName = $table->getTable() . '_' . $column . '_' . str_replace(['$.', '.'], ['', '_'], $path) . '_index';
                DB::statement("ALTER TABLE {$table->getTable()} ADD INDEX {$indexName} ((CAST(JSON_EXTRACT({$column}, '{$path}') AS CHAR(255))))");
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL supports GIN indexes on JSONB
            $indexName = $table->getTable() . '_' . $column . '_gin_index';
            DB::statement("CREATE INDEX {$indexName} ON {$table->getTable()} USING GIN ({$column})");
        }
    }
}
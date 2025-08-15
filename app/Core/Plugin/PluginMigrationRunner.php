<?php

namespace App\Core\Plugin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Blueprint;

class PluginMigrationRunner
{
    protected string $migrationsTable = 'plugin_migrations';

    /**
     * Run plugin migrations
     */
    public function runMigrations(string $pluginId, string $migrationsPath): bool
    {
        try {
            if (!File::exists($migrationsPath)) {
                Log::info("No migrations found for plugin: {$pluginId}");
                return true;
            }

            $migrations = $this->getMigrationFiles($migrationsPath);
            $ranMigrations = $this->getRanMigrations($pluginId);
            $batch = $this->getNextBatchNumber($pluginId);

            $newMigrations = array_diff($migrations, $ranMigrations);

            if (empty($newMigrations)) {
                Log::info("No new migrations to run for plugin: {$pluginId}");
                return true;
            }

            foreach ($newMigrations as $migration) {
                $this->runMigration($pluginId, $migration, $migrationsPath, $batch);
            }

            Log::info("Successfully ran " . count($newMigrations) . " migrations for plugin: {$pluginId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to run migrations for plugin: {$pluginId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Rollback plugin migrations
     */
    public function rollbackMigrations(string $pluginId, int $steps = 1): bool
    {
        try {
            $migrations = $this->getRanMigrationsForRollback($pluginId, $steps);

            if ($migrations->isEmpty()) {
                Log::info("No migrations to rollback for plugin: {$pluginId}");
                return true;
            }

            foreach ($migrations as $migration) {
                $this->rollbackMigration($pluginId, $migration->migration, $migration->batch);
            }

            Log::info("Successfully rolled back {$migrations->count()} migrations for plugin: {$pluginId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to rollback migrations for plugin: {$pluginId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Check if plugin has pending migrations
     */
    public function hasPendingMigrations(string $pluginId, string $migrationsPath): bool
    {
        if (!File::exists($migrationsPath)) {
            return false;
        }

        $migrations = $this->getMigrationFiles($migrationsPath);
        $ranMigrations = $this->getRanMigrations($pluginId);

        return count(array_diff($migrations, $ranMigrations)) > 0;
    }

    /**
     * Get migration files from directory
     */
    protected function getMigrationFiles(string $path): array
    {
        $files = File::glob($path . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_/', $filename)) {
                $migrations[] = $filename;
            }
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get ran migrations for plugin
     */
    protected function getRanMigrations(string $pluginId): array
    {
        return DB::table($this->migrationsTable)
            ->where('plugin_id', $pluginId)
            ->orderBy('migration')
            ->pluck('migration')
            ->toArray();
    }

    /**
     * Get ran migrations for rollback
     */
    protected function getRanMigrationsForRollback(string $pluginId, int $steps): \Illuminate\Support\Collection
    {
        return DB::table($this->migrationsTable)
            ->where('plugin_id', $pluginId)
            ->orderByDesc('batch')
            ->orderByDesc('migration')
            ->limit($steps)
            ->get();
    }

    /**
     * Get next batch number
     */
    protected function getNextBatchNumber(string $pluginId): int
    {
        $lastBatch = DB::table($this->migrationsTable)
            ->where('plugin_id', $pluginId)
            ->max('batch');

        return ($lastBatch ?? 0) + 1;
    }

    /**
     * Run a single migration
     */
    protected function runMigration(string $pluginId, string $migration, string $path, int $batch): void
    {
        $migrationPath = $path . '/' . $migration . '.php';
        
        if (!File::exists($migrationPath)) {
            throw new \Exception("Migration file not found: {$migrationPath}");
        }

        // Include the migration file
        $migrationClass = require $migrationPath;
        
        if (!is_object($migrationClass)) {
            throw new \Exception("Migration file must return a migration class instance: {$migration}");
        }

        // Run the migration
        DB::transaction(function () use ($pluginId, $migration, $migrationClass, $batch) {
            $migrationClass->up();
            
            // Record the migration
            DB::table($this->migrationsTable)->insert([
                'plugin_id' => $pluginId,
                'migration' => $migration,
                'batch' => $batch,
                'migrated_at' => now(),
            ]);
        });

        Log::info("Ran migration: {$pluginId}:{$migration}");
    }

    /**
     * Rollback a single migration
     */
    protected function rollbackMigration(string $pluginId, string $migration, int $batch): void
    {
        $plugin = app(PluginManager::class)->getPlugin($pluginId);
        
        if (!$plugin) {
            throw new \Exception("Plugin not found: {$pluginId}");
        }

        $migrationPath = $plugin->getPath() . '/database/migrations/' . $migration . '.php';
        
        if (!File::exists($migrationPath)) {
            Log::warning("Migration file not found for rollback: {$migrationPath}");
            // Still remove from database
            DB::table($this->migrationsTable)
                ->where('plugin_id', $pluginId)
                ->where('migration', $migration)
                ->delete();
            return;
        }

        // Include the migration file
        $migrationClass = require $migrationPath;
        
        if (!is_object($migrationClass)) {
            throw new \Exception("Migration file must return a migration class instance: {$migration}");
        }

        // Rollback the migration
        DB::transaction(function () use ($pluginId, $migration, $migrationClass) {
            $migrationClass->down();
            
            // Remove the migration record
            DB::table($this->migrationsTable)
                ->where('plugin_id', $pluginId)
                ->where('migration', $migration)
                ->delete();
        });

        Log::info("Rolled back migration: {$pluginId}:{$migration}");
    }

    /**
     * Check if plugin tables exist
     */
    public function tablesExist(string $pluginId): bool
    {
        $plugin = app(PluginManager::class)->getPlugin($pluginId);
        
        if (!$plugin) {
            return false;
        }

        // Get expected table names from plugin manifest or migrations
        $expectedTables = $this->getExpectedTables($plugin);
        
        foreach ($expectedTables as $table) {
            if (Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get expected table names for plugin
     */
    protected function getExpectedTables(Plugin $plugin): array
    {
        $tables = [];
        $pluginId = $plugin->getId();
        
        // Common table patterns
        $commonPatterns = [
            $pluginId . '_posts',
            $pluginId . '_categories',
            $pluginId . '_tags',
            $pluginId . 's', // e.g., blogs
        ];

        foreach ($commonPatterns as $pattern) {
            $tables[] = $pattern;
        }

        return $tables;
    }

    /**
     * Get migration status for plugin
     */
    public function getMigrationStatus(string $pluginId, string $migrationsPath): array
    {
        $allMigrations = $this->getMigrationFiles($migrationsPath);
        $ranMigrations = $this->getRanMigrations($pluginId);

        $status = [];
        foreach ($allMigrations as $migration) {
            $status[] = [
                'migration' => $migration,
                'status' => in_array($migration, $ranMigrations) ? 'ran' : 'pending',
                'batch' => $this->getMigrationBatch($pluginId, $migration),
            ];
        }

        return $status;
    }

    /**
     * Get migration batch number
     */
    protected function getMigrationBatch(string $pluginId, string $migration): ?int
    {
        return DB::table($this->migrationsTable)
            ->where('plugin_id', $pluginId)
            ->where('migration', $migration)
            ->value('batch');
    }

    /**
     * Reset all migrations for plugin
     */
    public function resetMigrations(string $pluginId): bool
    {
        try {
            // Get all migrations in reverse order
            $migrations = DB::table($this->migrationsTable)
                ->where('plugin_id', $pluginId)
                ->orderByDesc('batch')
                ->orderByDesc('migration')
                ->get();

            foreach ($migrations as $migration) {
                $this->rollbackMigration($pluginId, $migration->migration, $migration->batch);
            }

            Log::info("Reset all migrations for plugin: {$pluginId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to reset migrations for plugin: {$pluginId}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

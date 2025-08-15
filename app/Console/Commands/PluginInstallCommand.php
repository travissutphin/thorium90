<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Plugin\PluginManager;
use App\Core\Plugin\PluginMigrationRunner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PluginInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'plugin:install 
                            {plugin : The plugin ID to install}
                            {--enable : Enable the plugin after installation}
                            {--migrate : Run migrations after installation}
                            {--force : Force installation even if plugin exists}';

    /**
     * The console command description.
     */
    protected $description = 'Install a plugin with safety checks and migration handling';

    protected PluginManager $pluginManager;
    protected PluginMigrationRunner $migrationRunner;

    public function __construct(PluginManager $pluginManager, PluginMigrationRunner $migrationRunner)
    {
        parent::__construct();
        $this->pluginManager = $pluginManager;
        $this->migrationRunner = $migrationRunner;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pluginId = $this->argument('plugin');
        $enable = $this->option('enable');
        $migrate = $this->option('migrate');
        $force = $this->option('force');

        $this->info("Installing plugin: {$pluginId}");

        try {
            // Step 1: Discover plugins to ensure plugin exists
            $this->pluginManager->discoverPlugins();
            
            // Step 2: Check if plugin exists
            $plugin = $this->pluginManager->getPlugin($pluginId);
            if (!$plugin) {
                $this->error("Plugin '{$pluginId}' not found in plugins directory.");
                $this->info("Make sure the plugin is placed in: " . base_path("plugins/{$pluginId}/"));
                return 1;
            }

            // Step 3: Check if already installed
            if ($this->isPluginInstalled($pluginId) && !$force) {
                $this->error("Plugin '{$pluginId}' is already installed. Use --force to reinstall.");
                return 1;
            }

            // Step 4: Safety checks
            if (!$this->performSafetyChecks($pluginId, $force)) {
                return 1;
            }

            // Step 5: Install plugin
            if (!$this->installPlugin($pluginId, $plugin)) {
                return 1;
            }

            // Step 6: Run migrations if requested
            if ($migrate) {
                if (!$this->runPluginMigrations($pluginId, $plugin)) {
                    $this->error("Plugin installed but migrations failed. You can run them manually with: php artisan plugin:migrate {$pluginId}");
                    return 1;
                }
            }

            // Step 7: Enable plugin if requested
            if ($enable) {
                if (!$this->enablePlugin($pluginId)) {
                    $this->error("Plugin installed but failed to enable. You can enable it manually with: php artisan plugin:enable {$pluginId}");
                    return 1;
                }
            }

            $this->info("✅ Plugin '{$pluginId}' installed successfully!");
            
            if ($migrate) {
                $this->info("✅ Migrations completed successfully!");
            }
            
            if ($enable) {
                $this->info("✅ Plugin enabled successfully!");
            }

            $this->displayNextSteps($pluginId, $enable, $migrate);

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to install plugin '{$pluginId}': " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Check if plugin is already installed
     */
    protected function isPluginInstalled(string $pluginId): bool
    {
        return DB::table('plugin_states')
            ->where('plugin_id', $pluginId)
            ->exists();
    }

    /**
     * Perform safety checks before installation
     */
    protected function performSafetyChecks(string $pluginId, bool $force): bool
    {
        $this->info("Performing safety checks...");

        // Check if plugin tables already exist
        if ($this->migrationRunner->tablesExist($pluginId) && !$force) {
            $this->error("Plugin tables already exist in database. This suggests the plugin was previously installed.");
            $this->error("Use --force to proceed anyway, or uninstall the plugin first.");
            return false;
        }

        // Check for conflicting plugins
        if ($this->hasConflictingPlugins($pluginId)) {
            $this->error("Conflicting plugins detected. Please resolve conflicts before installing.");
            return false;
        }

        // Check dependencies
        $plugin = $this->pluginManager->getPlugin($pluginId);
        $dependencies = $plugin->getManifest()['dependencies'] ?? [];
        
        if (!empty($dependencies)) {
            $this->info("Checking dependencies...");
            foreach ($dependencies as $depId => $version) {
                if (!$this->isDependencyMet($depId, $version)) {
                    $this->error("Dependency not met: {$depId} (required: {$version})");
                    return false;
                }
            }
        }

        $this->info("✅ Safety checks passed!");
        return true;
    }

    /**
     * Install the plugin
     */
    protected function installPlugin(string $pluginId, $plugin): bool
    {
        $this->info("Installing plugin...");

        try {
            // Record plugin installation
            DB::table('plugin_states')->updateOrInsert(
                ['plugin_id' => $pluginId],
                [
                    'plugin_id' => $pluginId,
                    'version' => $plugin->getVersion(),
                    'enabled' => false,
                    'installed_at' => now(),
                    'migration_batch' => 0,
                    'settings' => json_encode([]),
                    'navigation' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return true;

        } catch (\Exception $e) {
            $this->error("Failed to record plugin installation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Run plugin migrations
     */
    protected function runPluginMigrations(string $pluginId, $plugin): bool
    {
        $this->info("Running plugin migrations...");

        $migrationsPath = $plugin->getPath() . '/database/migrations';
        
        if (!File::exists($migrationsPath)) {
            $this->info("No migrations found for plugin.");
            return true;
        }

        $success = $this->migrationRunner->runMigrations($pluginId, $migrationsPath);
        
        if ($success) {
            // Update migration batch in plugin state
            $batch = $this->migrationRunner->getNextBatchNumber($pluginId) - 1;
            DB::table('plugin_states')
                ->where('plugin_id', $pluginId)
                ->update(['migration_batch' => $batch]);
        }

        return $success;
    }

    /**
     * Enable the plugin
     */
    protected function enablePlugin(string $pluginId): bool
    {
        $this->info("Enabling plugin...");

        try {
            $success = $this->pluginManager->enablePlugin($pluginId);
            
            if ($success) {
                DB::table('plugin_states')
                    ->where('plugin_id', $pluginId)
                    ->update([
                        'enabled' => true,
                        'enabled_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            return $success;

        } catch (\Exception $e) {
            $this->error("Failed to enable plugin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check for conflicting plugins
     */
    protected function hasConflictingPlugins(string $pluginId): bool
    {
        // This could be enhanced to check for actual conflicts
        // For now, just return false
        return false;
    }

    /**
     * Check if dependency is met
     */
    protected function isDependencyMet(string $depId, string $requiredVersion): bool
    {
        $depPlugin = $this->pluginManager->getPlugin($depId);
        
        if (!$depPlugin) {
            return false;
        }

        if (!$this->pluginManager->isPluginEnabled($depId)) {
            return false;
        }

        return version_compare($depPlugin->getVersion(), $requiredVersion, '>=');
    }

    /**
     * Display next steps to the user
     */
    protected function displayNextSteps(string $pluginId, bool $enabled, bool $migrated): void
    {
        $this->info("");
        $this->info("📋 Next Steps:");

        if (!$migrated) {
            $this->info("• Run migrations: php artisan plugin:migrate {$pluginId}");
        }

        if (!$enabled) {
            $this->info("• Enable plugin: php artisan plugin:enable {$pluginId}");
        }

        $this->info("• View plugin status: php artisan plugin:status {$pluginId}");
        $this->info("• Configure plugin: Visit Admin > Plugins > {$pluginId}");
        
        $plugin = $this->pluginManager->getPlugin($pluginId);
        if ($plugin && !empty($plugin->getManifest()['permissions'] ?? [])) {
            $this->info("• Assign permissions to users/roles as needed");
        }
    }
}

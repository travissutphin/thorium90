<?php

namespace App\Console\Commands;

use App\Services\DatabaseConfigurationService;
use Illuminate\Console\Command;

class ValidateDatabaseConfigCommand extends Command
{
    protected $signature = 'thorium90:validate-database 
                            {--detailed : Show detailed configuration information}
                            {--recommendations : Show production recommendations}';

    protected $description = 'Validate database configuration and show recommendations';

    public function handle(DatabaseConfigurationService $service)
    {
        $this->info('🔍 Validating Database Configuration...');
        $this->newLine();

        // Validate current configuration
        $validation = $service->validateConfiguration();

        // Show basic info
        $this->line('<fg=cyan>Database Information:</fg=cyan>');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Driver', $validation['info']['driver'] ?? 'Unknown'],
                ['Database', $validation['info']['database'] ?? 'Unknown'],
                ['Version', $validation['info']['version'] ?? 'Unknown'],
                ['Charset/Encoding', $validation['info']['charset'] ?? $validation['info']['encoding'] ?? 'Unknown'],
            ]
        );

        // Show validation results
        if ($validation['valid']) {
            $this->info('✅ Database configuration is valid');
        } else {
            $this->error('❌ Database configuration has errors');
        }

        // Show errors
        if (!empty($validation['errors'])) {
            $this->newLine();
            $this->error('🚨 Errors:');
            foreach ($validation['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }

        // Show warnings
        if (!empty($validation['warnings'])) {
            $this->newLine();
            $this->warn('⚠️  Warnings:');
            foreach ($validation['warnings'] as $warning) {
                $this->line("  • {$warning}");
            }
        }

        // Show detailed info if requested
        if ($this->option('detailed')) {
            $this->newLine();
            $this->line('<fg=cyan>PHP Extension Status:</fg=cyan>');
            $extensions = $service->checkRequiredExtensions();
            
            foreach ($extensions as $driver => $driverExtensions) {
                $this->line("<fg=yellow>{$driver}:</fg=yellow>");
                foreach ($driverExtensions as $extension => $loaded) {
                    $status = $loaded ? '✅' : '❌';
                    $this->line("  {$status} {$extension}");
                }
            }

            if (isset($validation['info'])) {
                $this->newLine();
                $this->line('<fg=cyan>Detailed Configuration:</fg=cyan>');
                foreach ($validation['info'] as $key => $value) {
                    if (!in_array($key, ['driver', 'database', 'version', 'charset', 'encoding'])) {
                        $this->line("  <fg=yellow>{$key}:</fg=yellow> {$value}");
                    }
                }
            }
        }

        // Show recommendations if requested
        if ($this->option('recommendations')) {
            $recommendations = $service->getProductionRecommendations();
            
            if (!empty($recommendations)) {
                $this->newLine();
                $this->line('<fg=cyan>Production Recommendations:</fg=cyan>');
                foreach ($recommendations as $recommendation) {
                    $this->line("  • {$recommendation}");
                }
            }
        }

        $this->newLine();
        
        if ($validation['valid'] && empty($validation['warnings'])) {
            $this->info('🎉 Your database configuration looks great!');
            return Command::SUCCESS;
        } elseif ($validation['valid']) {
            $this->warn('✅ Configuration is functional but could be improved.');
            $this->line('Run with --recommendations flag for production tips.');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Please fix the errors above before proceeding.');
            return Command::FAILURE;
        }
    }
}
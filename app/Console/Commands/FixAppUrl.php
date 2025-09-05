<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixAppUrl extends Command
{
    protected $signature = 'thorium90:fix-urls {--http : Convert to HTTP for local development}';
    protected $description = 'Fix APP_URL for local development (converts HTTPS to HTTP)';

    public function handle()
    {
        $this->info('🔧 Thorium90 URL Fix');
        
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            $this->error('No .env file found');
            return 1;
        }
        
        $env = File::get($envPath);
        $currentUrl = config('app.url');
        
        $this->line("Current APP_URL: {$currentUrl}");
        
        // Simple conversion logic
        if ($this->option('http') || str_contains($currentUrl, 'https://localhost') || str_contains($currentUrl, 'https://127.0.0.1')) {
            $newUrl = str_replace('https://', 'http://', $currentUrl);
            
            if ($newUrl !== $currentUrl) {
                $env = preg_replace('/APP_URL=.*/', "APP_URL={$newUrl}", $env);
                File::put($envPath, $env);
                
                $this->call('config:clear');
                
                $this->info("✅ Updated APP_URL to: {$newUrl}");
                $this->line('Media files should now load properly!');
            } else {
                $this->info('✅ APP_URL already using HTTP');
            }
        } else {
            $this->info('✅ APP_URL looks good - no changes needed');
        }
        
        return 0;
    }
}
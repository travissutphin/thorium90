<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateEmailConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate email configuration for Resend and other mail drivers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Validating email configuration...');
        $this->newLine();

        $hasErrors = false;

        // Check default mailer
        $mailer = config('mail.default');
        $this->line("📧 Default mailer: <comment>{$mailer}</comment>");

        // Validate mailer configuration
        if (!$mailer) {
            $this->error('✗ No default mailer configured');
            $hasErrors = true;
        } else {
            $this->info('✓ Default mailer is configured');
        }

        // Check if the configured mailer exists
        $mailers = config('mail.mailers', []);
        if (!array_key_exists($mailer, $mailers)) {
            $this->error("✗ Mailer '{$mailer}' is not defined in mail.mailers configuration");
            $hasErrors = true;
        } else {
            $this->info("✓ Mailer '{$mailer}' configuration exists");
        }

        $this->newLine();

        // Resend-specific validation
        if ($mailer === 'resend') {
            $this->line('🚀 <comment>Resend Configuration:</comment>');
            
            $apiKey = config('mail.mailers.resend.key');
            if ($apiKey) {
                $this->info('✓ Resend API key is configured');
                
                // Check key format
                if (str_starts_with($apiKey, 're_')) {
                    $this->info('✓ Production API key format is valid');
                } elseif (str_starts_with($apiKey, 're_test_')) {
                    $this->warn('⚠ Using test API key (suitable for development)');
                } else {
                    $this->warn('⚠ API key format may be invalid (should start with "re_" or "re_test_")');
                }

                // Check if it's a placeholder
                if ($apiKey === 'your_resend_api_key_here') {
                    $this->error('✗ API key appears to be a placeholder - please set your actual Resend API key');
                    $hasErrors = true;
                }
            } else {
                $this->error('✗ Resend API key is not configured (RESEND_API_KEY)');
                $hasErrors = true;
            }
        }

        $this->newLine();

        // Check from address configuration
        $this->line('📬 <comment>From Address Configuration:</comment>');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($fromAddress) {
            $this->line("From address: <comment>{$fromAddress}</comment>");
            
            if (filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
                $this->info('✓ From address is a valid email format');
                
                // Check if it's still the default
                if ($fromAddress === 'hello@example.com') {
                    $this->warn('⚠ Using default Laravel from address - consider updating for production');
                }
            } else {
                $this->error('✗ From address is not a valid email format');
                $hasErrors = true;
            }
        } else {
            $this->error('✗ From address is not configured (MAIL_FROM_ADDRESS)');
            $hasErrors = true;
        }

        if ($fromName) {
            $this->line("From name: <comment>{$fromName}</comment>");
            $this->info('✓ From name is configured');
            
            if ($fromName === 'Example') {
                $this->warn('⚠ Using default Laravel from name - consider updating for production');
            }
        } else {
            $this->warn('⚠ From name is not configured (MAIL_FROM_NAME)');
        }

        $this->newLine();

        // Environment-specific checks
        $this->line('🌍 <comment>Environment-Specific Checks:</comment>');
        $environment = config('app.env');
        $this->line("Environment: <comment>{$environment}</comment>");

        switch ($environment) {
            case 'production':
                if ($mailer === 'log') {
                    $this->error('✗ Using log driver in production - emails will not be sent');
                    $hasErrors = true;
                } elseif ($mailer === 'resend') {
                    $apiKey = config('mail.mailers.resend.key');
                    if ($apiKey && str_starts_with($apiKey, 're_test_')) {
                        $this->warn('⚠ Using test API key in production environment');
                    }
                }
                break;
                
            case 'local':
            case 'development':
                if ($mailer === 'resend') {
                    $this->info('✓ Using Resend in development - ensure you have a valid API key');
                } elseif ($mailer === 'log') {
                    $this->info('✓ Using log driver in development - emails will be logged');
                }
                break;
        }

        $this->newLine();

        // Additional configuration checks
        $this->line('⚙️ <comment>Additional Configuration:</comment>');

        // Check queue configuration
        $queueDefault = config('queue.default');
        $this->line("Queue driver: <comment>{$queueDefault}</comment>");
        
        if ($queueDefault === 'sync') {
            $this->warn('⚠ Using sync queue driver - consider using database/redis for better performance');
        } else {
            $this->info('✓ Using asynchronous queue driver for better email performance');
        }

        // Check if mail logging is configured
        $mailLogChannel = config('mail.log_channel');
        if ($mailLogChannel) {
            $this->info("✓ Mail logging configured to channel: {$mailLogChannel}");
        }

        $this->newLine();

        // Summary
        if ($hasErrors) {
            $this->error('❌ Email configuration validation completed with errors');
            $this->line('Please fix the errors above before using email functionality.');
            return 1;
        } else {
            $this->info('✅ Email configuration validation completed successfully!');
            $this->line('Your email configuration appears to be properly set up.');
            
            if ($mailer === 'resend') {
                $this->newLine();
                $this->line('💡 <comment>Next steps:</comment>');
                $this->line('• Test email sending: <info>php artisan email:test your-email@example.com</info>');
                $this->line('• Run email tests: <info>php artisan test --filter=ResendEmailTest</info>');
                
                if ($environment === 'production') {
                    $this->line('• Ensure your domain is verified in the Resend dashboard');
                    $this->line('• Monitor email delivery in the Resend dashboard');
                }
            }
        }

        return 0;
    }
}

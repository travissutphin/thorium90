<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email} {--subject=Test Email} {--message=This is a test email from your Laravel application using Resend!}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email via the configured mail driver (Resend)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $subject = $this->option('subject');
        $message = $this->option('message');

        $this->info('Sending test email...');
        $this->line("To: {$email}");
        $this->line("Subject: {$subject}");
        $this->line("Driver: " . config('mail.default'));

        try {
            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->info("✓ Test email sent successfully to: {$email}");
            
            if (config('mail.default') === 'log') {
                $this->warn('Note: Using log driver - check storage/logs/laravel.log for the email content');
            }
            
        } catch (\Exception $e) {
            $this->error("✗ Failed to send test email: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

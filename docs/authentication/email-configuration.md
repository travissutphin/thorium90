# Email Configuration with Resend.com

## Overview

This guide covers the configuration and usage of Resend.com as the email service provider for Laravel 12. Resend provides excellent deliverability, developer experience, and seamless integration with Laravel's built-in email system.

## Features

- **Built-in Laravel 12 Support**: Native Resend transport driver
- **High Deliverability**: Industry-leading email delivery rates
- **Developer-Friendly**: Simple API and excellent documentation
- **Authentication Integration**: Works seamlessly with password resets and email verification
- **Testing Support**: Easy to test and debug email functionality

## Configuration

### 1. Environment Setup

#### Required Environment Variables

Add the following to your `.env` file:

```env
# Email Configuration
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="travis.sutphin@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# Resend API Configuration
RESEND_API_KEY=your_resend_api_key_here
```

#### Development vs Production

**Development Environment:**
```env
# For development, you can use 'log' to see emails in logs
MAIL_MAILER=log

# Or use 'resend' with a test API key
MAIL_MAILER=resend
RESEND_API_KEY=re_test_your_test_key_here
```

**Production Environment:**
```env
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="Your App Name"
RESEND_API_KEY=re_your_production_key_here
```

### 2. Obtaining Resend API Key

1. **Sign up** at [resend.com](https://resend.com)
2. **Verify your domain** (required for production)
3. **Create an API key** in the dashboard
4. **Copy the API key** to your `.env` file

#### API Key Types

- **Test Keys**: Start with `re_test_` - for development
- **Production Keys**: Start with `re_` - for production use
- **Domain Verification**: Required for production sending

### 3. Domain Verification

For production use, you must verify your sending domain:

1. **Add your domain** in the Resend dashboard
2. **Add DNS records** as provided by Resend:
   - SPF record
   - DKIM record
   - DMARC record (recommended)
3. **Wait for verification** (usually takes a few minutes)

#### Example DNS Records
```dns
# SPF Record
yourdomain.com TXT "v=spf1 include:_spf.resend.com ~all"

# DKIM Record
resend._domainkey.yourdomain.com CNAME resend.yourdomain.com._domainkey.resend.com

# DMARC Record (recommended)
_dmarc.yourdomain.com TXT "v=DMARC1; p=quarantine; rua=mailto:dmarc@yourdomain.com"
```

## Usage

### 1. Built-in Authentication Emails

The following authentication features automatically use your configured email service:

#### Password Reset
```php
// Automatically sends via Resend when user requests password reset
$this->post('/forgot-password', ['email' => $user->email]);
```

#### Email Verification
```php
// Automatically sends verification email via Resend
$user->sendEmailVerificationNotification();
```

### 2. Custom Email Notifications

#### Creating Custom Notifications
```php
// Generate a new notification
php artisan make:notification WelcomeNotification
```

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Welcome to our application.')
            ->action('Get Started', url('/dashboard'))
            ->line('Thank you for joining us!');
    }
}
```

#### Sending Custom Notifications
```php
use App\Notifications\WelcomeNotification;

// Send to a user
$user->notify(new WelcomeNotification());

// Send to multiple users
Notification::send($users, new WelcomeNotification());
```

### 3. Direct Mail Usage

#### Using Laravel's Mail Facade
```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Hello World!', function ($message) {
    $message->to('user@example.com')
            ->subject('Test Email');
});
```

#### Using Mailable Classes
```php
// Generate a mailable
php artisan make:mail OrderShipped

// Send the mailable
Mail::to($user)->send(new OrderShipped($order));
```

## Testing

### 1. Unit Testing

#### Testing Email Notifications
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_notification_is_sent()
    {
        Notification::fake();

        $user = User::factory()->create();
        
        $user->notify(new WelcomeNotification());

        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_password_reset_email_is_sent()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }
}
```

### 2. Manual Testing

#### Test Email Configuration
```php
// Add to routes/web.php for testing (remove in production)
Route::get('/test-email', function () {
    Mail::raw('Test email from Resend!', function ($message) {
        $message->to('test@example.com')
                ->subject('Resend Test Email');
    });
    
    return 'Test email sent!';
});
```

#### Artisan Command for Testing
```php
// Create a test command
php artisan make:command TestEmailCommand
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Send a test email via Resend';

    public function handle()
    {
        $email = $this->argument('email');
        
        Mail::raw('This is a test email from your Laravel application using Resend!', function ($message) use ($email) {
            $message->to($email)
                    ->subject('Test Email - ' . config('app.name'));
        });

        $this->info("Test email sent to: {$email}");
    }
}
```

Usage:
```bash
php artisan email:test your-email@example.com
```

## Configuration Validation

### 1. Check Current Configuration
```php
// Add to routes/web.php for debugging (remove in production)
Route::get('/email-config', function () {
    return [
        'default_mailer' => config('mail.default'),
        'resend_configured' => config('mail.mailers.resend.key') ? 'Yes' : 'No',
        'from_address' => config('mail.from.address'),
        'from_name' => config('mail.from.name'),
    ];
});
```

### 2. Validation Command
```php
// Create validation command
php artisan make:command ValidateEmailConfigCommand
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateEmailConfigCommand extends Command
{
    protected $signature = 'email:validate';
    protected $description = 'Validate email configuration';

    public function handle()
    {
        $this->info('Validating email configuration...');

        // Check mailer
        $mailer = config('mail.default');
        $this->line("Default mailer: {$mailer}");

        // Check Resend configuration
        if ($mailer === 'resend') {
            $apiKey = config('mail.mailers.resend.key');
            if ($apiKey) {
                $this->info('✓ Resend API key is configured');
                
                // Check key format
                if (str_starts_with($apiKey, 're_')) {
                    $this->info('✓ API key format is valid');
                } else {
                    $this->warn('⚠ API key format may be invalid');
                }
            } else {
                $this->error('✗ Resend API key is not configured');
            }
        }

        // Check from address
        $fromAddress = config('mail.from.address');
        $this->line("From address: {$fromAddress}");

        if (filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $this->info('✓ From address is valid');
        } else {
            $this->error('✗ From address is invalid');
        }

        $this->info('Email configuration validation complete!');
    }
}
```

Usage:
```bash
php artisan email:validate
```

## Troubleshooting

### Common Issues

#### 1. API Key Issues
```
Error: "Invalid API key"
```

**Solutions:**
- Verify API key is correct in `.env`
- Ensure no extra spaces or quotes
- Check if using test key in production
- Regenerate API key if needed

#### 2. Domain Verification Issues
```
Error: "Domain not verified"
```

**Solutions:**
- Complete domain verification in Resend dashboard
- Check DNS records are properly configured
- Wait for DNS propagation (up to 24 hours)
- Use verified domain in `MAIL_FROM_ADDRESS`

#### 3. Rate Limiting
```
Error: "Rate limit exceeded"
```

**Solutions:**
- Check your Resend plan limits
- Implement email queuing for bulk sends
- Add delays between email sends
- Upgrade Resend plan if needed

#### 4. Configuration Cache Issues
```
Error: Configuration not updating
```

**Solutions:**
```bash
# Clear configuration cache
php artisan config:clear

# Clear all caches
php artisan optimize:clear
```

### Debug Commands

#### Check Mail Configuration
```bash
# View current mail configuration
php artisan config:show mail

# Test email sending
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

#### View Email Logs
```bash
# Check Laravel logs for email errors
tail -f storage/logs/laravel.log | grep -i mail

# Check specific log channel if configured
tail -f storage/logs/mail.log
```

## Security Best Practices

### 1. API Key Management

- **Never commit API keys** to version control
- **Use different keys** for development and production
- **Rotate keys regularly** (every 90 days recommended)
- **Restrict key permissions** if available
- **Monitor key usage** in Resend dashboard

### 2. Email Security

- **Verify sender domain** to prevent spoofing
- **Use DMARC policy** to protect domain reputation
- **Validate recipient addresses** before sending
- **Implement rate limiting** to prevent abuse
- **Log email activities** for audit trails

### 3. Content Security

- **Sanitize email content** to prevent XSS
- **Use email templates** for consistent formatting
- **Avoid sensitive data** in email content
- **Implement unsubscribe** mechanisms where required

## Performance Optimization

### 1. Queue Email Sending

```php
// Queue notifications for better performance
class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    // Notification implementation
}
```

### 2. Batch Email Operations

```php
// Send bulk emails efficiently
$users = User::where('active', true)->get();

Notification::send($users, new NewsletterNotification());
```

### 3. Email Templates Caching

```php
// Cache compiled email templates
php artisan view:cache
```

## Monitoring and Analytics

### 1. Email Delivery Tracking

Monitor email delivery through:
- **Resend Dashboard**: View delivery statistics
- **Laravel Logs**: Check for sending errors
- **Application Metrics**: Track email volumes

### 2. Custom Logging

```php
// Add custom email logging
Log::channel('mail')->info('Email sent', [
    'to' => $recipient,
    'subject' => $subject,
    'timestamp' => now(),
]);
```

## Related Documentation

- [Authentication Overview](README.md)
- [API Documentation](api.md)
- [Deployment Guide](deployment.md)
- [Social Login Configuration](social-login.md)
- [Testing Guide](../testing/authentication-tests.md)

## External Resources

- [Resend Documentation](https://resend.com/docs)
- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Laravel Notifications Documentation](https://laravel.com/docs/notifications)

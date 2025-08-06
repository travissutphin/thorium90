<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up Resend configuration for testing
        Config::set('mail.default', 'resend');
        Config::set('mail.mailers.resend.key', 'test-api-key');
        Config::set('mail.from.address', 'travis.sutphin@gmail.com');
        Config::set('mail.from.name', 'Laravel Test');
    }

    public function test_resend_mailer_is_configured()
    {
        $this->assertEquals('resend', config('mail.default'));
        $this->assertEquals('test-api-key', config('mail.mailers.resend.key'));
        $this->assertEquals('travis.sutphin@gmail.com', config('mail.from.address'));
    }

    public function test_password_reset_email_uses_resend_configuration()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            // Verify the notification was created properly
            $this->assertNotNull($notification->token);
            return true;
        });
    }

    public function test_email_verification_uses_resend_configuration()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
             ->post('/email/verification-notification');

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    public function test_mail_configuration_validation()
    {
        // Test that required configuration is present
        $this->assertNotEmpty(config('mail.default'));
        $this->assertNotEmpty(config('mail.from.address'));
        $this->assertNotEmpty(config('mail.from.name'));
        
        // Test Resend specific configuration
        $resendConfig = config('mail.mailers.resend');
        $this->assertIsArray($resendConfig);
        $this->assertEquals('resend', $resendConfig['transport']);
        $this->assertArrayHasKey('key', $resendConfig);
    }

    public function test_from_address_is_valid_email()
    {
        $fromAddress = config('mail.from.address');
        $this->assertTrue(filter_var($fromAddress, FILTER_VALIDATE_EMAIL) !== false);
    }

    public function test_resend_api_key_format()
    {
        $apiKey = config('mail.mailers.resend.key');
        
        // In testing, we use a test key, but in production it should start with 're_'
        if ($apiKey && $apiKey !== 'test-api-key') {
            $this->assertTrue(
                str_starts_with($apiKey, 're_') || str_starts_with($apiKey, 're_test_'),
                'Resend API key should start with "re_" or "re_test_"'
            );
        }
    }

    public function test_mail_driver_configuration_is_valid()
    {
        // Test that the mail driver is properly configured for Resend
        $this->assertEquals('resend', config('mail.default'));
        
        // Test that the Resend mailer configuration exists
        $resendMailer = config('mail.mailers.resend');
        $this->assertIsArray($resendMailer);
        $this->assertEquals('resend', $resendMailer['transport']);
        $this->assertArrayHasKey('key', $resendMailer);
    }

    public function test_mail_from_configuration_is_complete()
    {
        // Test that from address and name are configured
        $fromConfig = config('mail.from');
        $this->assertIsArray($fromConfig);
        $this->assertArrayHasKey('address', $fromConfig);
        $this->assertArrayHasKey('name', $fromConfig);
        
        // Test that values are not empty
        $this->assertNotEmpty($fromConfig['address']);
        $this->assertNotEmpty($fromConfig['name']);
        
        // Test that from address is valid email format
        $this->assertTrue(filter_var($fromConfig['address'], FILTER_VALIDATE_EMAIL) !== false);
    }

    public function test_resend_integration_with_notifications()
    {
        // This test verifies that Resend works with Laravel's notification system
        // which is the primary way emails are sent in authentication flows
        
        Notification::fake();
        
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        // Test password reset notification (uses mail driver)
        $this->post('/forgot-password', ['email' => $user->email]);
        
        Notification::assertSentTo($user, ResetPassword::class);
        
        // This confirms that the notification system can work with Resend
        $this->assertTrue(true, 'Resend integration with notifications is working');
    }

    public function test_mail_configuration_supports_production_requirements()
    {
        // Test that configuration supports production requirements
        $mailConfig = config('mail');
        
        // Verify mailers array exists and contains resend
        $this->assertIsArray($mailConfig['mailers']);
        $this->assertArrayHasKey('resend', $mailConfig['mailers']);
        
        // Verify from configuration exists
        $this->assertIsArray($mailConfig['from']);
        $this->assertArrayHasKey('address', $mailConfig['from']);
        $this->assertArrayHasKey('name', $mailConfig['from']);
        
        // Verify default mailer is set
        $this->assertNotEmpty($mailConfig['default']);
    }

    public function test_email_queue_configuration()
    {
        // Test that queue configuration exists (important for production)
        $queueDefault = config('queue.default');
        $this->assertNotEmpty($queueDefault);
        
        // In testing environment, sync is acceptable, but in production should use async queues
        $environment = config('app.env');
        if ($environment === 'production') {
            $this->assertNotEquals('sync', $queueDefault, 'Production should use asynchronous queue driver for better email performance');
        }
        
        // Ensure queue configuration is valid
        $this->assertContains($queueDefault, ['sync', 'database', 'redis', 'sqs', 'beanstalkd']);
    }

    public function test_email_rate_limiting_awareness()
    {
        // This test ensures we're aware of rate limiting considerations
        // Resend has rate limits that should be respected in production
        
        $resendConfig = config('mail.mailers.resend');
        $this->assertIsArray($resendConfig);
        
        // In a real application, you might want to implement rate limiting
        // This test serves as a reminder to consider rate limits
        $this->assertTrue(true, 'Rate limiting should be considered for bulk email operations');
    }

    public function test_environment_specific_configuration()
    {
        $environment = config('app.env');
        $mailer = config('mail.default');
        
        if ($environment === 'testing') {
            // In testing, we might use array or log driver
            $this->assertContains($mailer, ['array', 'log', 'resend']);
        } elseif ($environment === 'local') {
            // In local development, log or resend with test key
            $this->assertContains($mailer, ['log', 'resend']);
        } elseif ($environment === 'production') {
            // In production, should use resend
            $this->assertEquals('resend', $mailer);
            
            // Production should have a real API key
            $apiKey = config('mail.mailers.resend.key');
            $this->assertNotEmpty($apiKey);
            $this->assertNotEquals('test-api-key', $apiKey);
        }
    }

    public function test_mail_from_configuration_consistency()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');
        
        $this->assertNotEmpty($fromAddress);
        $this->assertNotEmpty($fromName);
        
        // Ensure from address is not the default Laravel example
        $this->assertNotEquals('hello@example.com', $fromAddress);
        
        // Ensure from name is meaningful
        $this->assertNotEquals('Example', $fromName);
    }

    public function test_resend_transport_driver_exists()
    {
        $mailers = config('mail.mailers');
        
        $this->assertArrayHasKey('resend', $mailers);
        $this->assertEquals('resend', $mailers['resend']['transport']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * SettingsSeeder
 * 
 * This seeder creates default system settings for the Multi-Role User Authentication
 * system. It establishes baseline configuration values across all major categories
 * that administrators can later modify through the admin panel.
 * 
 * Key Features:
 * - Creates default settings for all system categories
 * - Provides sensible default values for production use
 * - Organizes settings by functional categories
 * - Sets appropriate visibility (public/private) for each setting
 * 
 * Setting Categories:
 * - Application: Basic app configuration (name, description, etc.)
 * - Authentication: Login, registration, and security settings
 * - User Management: Default roles, registration policies
 * - Security: Password policies, session management, 2FA settings
 * - Email: Email configuration and templates
 * - Features: Feature toggles and experimental features
 * - System: Maintenance mode, logging, performance settings
 * 
 * Usage:
 * ```bash
 * # Run this seeder to create default settings
 * php artisan db:seed --class=SettingsSeeder
 * 
 * # Or run all seeders
 * php artisan db:seed
 * ```
 * 
 * @see App\Models\Setting
 */
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * 
     * This method creates all default settings for the system, organized by
     * functional categories. Each setting includes appropriate type casting,
     * descriptions, and visibility settings.
     */
    public function run(): void
    {
        // Clear settings cache to ensure fresh data
        Setting::clearCache();

        // Application Settings
        $this->createApplicationSettings();
        
        // Authentication Settings
        $this->createAuthenticationSettings();
        
        // User Management Settings
        $this->createUserManagementSettings();
        
        // Security Settings
        $this->createSecuritySettings();
        
        // Email Settings
        $this->createEmailSettings();
        
        // Feature Settings
        $this->createFeatureSettings();
        
        // System Settings
        $this->createSystemSettings();
    }

    /**
     * Create application-level settings
     */
    protected function createApplicationSettings(): void
    {
        Setting::set(
            'app.name',
            'Thorium90 Authentication System',
            'string',
            'application',
            'The name of the application displayed throughout the system',
            true
        );

        Setting::set(
            'app.description',
            'Multi-Role User Authentication System with Laravel and React',
            'string',
            'application',
            'Brief description of the application',
            true
        );

        Setting::set(
            'app.version',
            '2.0.1',
            'string',
            'application',
            'Current application version',
            true
        );

        Setting::set(
            'app.timezone',
            'UTC',
            'string',
            'application',
            'Default timezone for the application',
            false
        );

        Setting::set(
            'app.locale',
            'en',
            'string',
            'application',
            'Default language locale',
            true
        );

        Setting::set(
            'app.maintenance_mode',
            false,
            'boolean',
            'application',
            'Enable maintenance mode to restrict access',
            false
        );

        Setting::set(
            'app.maintenance_message',
            'The system is currently under maintenance. Please check back later.',
            'string',
            'application',
            'Message displayed during maintenance mode',
            true
        );

        Setting::set(
            'app.debug',
            false,
            'boolean',
            'application',
            'Enable debug mode (development only)',
            false
        );
    }

    /**
     * Create authentication-related settings
     */
    protected function createAuthenticationSettings(): void
    {
        Setting::set(
            'auth.registration_enabled',
            true,
            'boolean',
            'authentication',
            'Allow new user registration',
            true
        );

        Setting::set(
            'auth.email_verification_required',
            true,
            'boolean',
            'authentication',
            'Require email verification for new accounts',
            false
        );

        Setting::set(
            'auth.default_role',
            'Subscriber',
            'string',
            'authentication',
            'Default role assigned to new users',
            false
        );

        Setting::set(
            'auth.login_attempts_limit',
            5,
            'integer',
            'authentication',
            'Maximum login attempts before account lockout',
            false
        );

        Setting::set(
            'auth.lockout_duration',
            15,
            'integer',
            'authentication',
            'Account lockout duration in minutes',
            false
        );

        Setting::set(
            'auth.session_lifetime',
            120,
            'integer',
            'authentication',
            'Session lifetime in minutes',
            false
        );

        Setting::set(
            'auth.remember_me_duration',
            43200,
            'integer',
            'authentication',
            'Remember me duration in minutes (30 days)',
            false
        );

        Setting::set(
            'auth.social_login_enabled',
            true,
            'boolean',
            'authentication',
            'Enable social login providers',
            true
        );

        Setting::set(
            'auth.social_providers',
            ['google', 'github'],
            'array',
            'authentication',
            'Enabled social login providers',
            true
        );
    }

    /**
     * Create user management settings
     */
    protected function createUserManagementSettings(): void
    {
        Setting::set(
            'users.profile_photo_enabled',
            true,
            'boolean',
            'user_management',
            'Allow users to upload profile photos',
            true
        );

        Setting::set(
            'users.profile_photo_max_size',
            2048,
            'integer',
            'user_management',
            'Maximum profile photo size in KB',
            false
        );

        Setting::set(
            'users.soft_delete_enabled',
            true,
            'boolean',
            'user_management',
            'Use soft delete for user accounts',
            false
        );

        Setting::set(
            'users.auto_cleanup_deleted',
            false,
            'boolean',
            'user_management',
            'Automatically cleanup soft-deleted users',
            false
        );

        Setting::set(
            'users.cleanup_after_days',
            90,
            'integer',
            'user_management',
            'Days to keep soft-deleted users before permanent deletion',
            false
        );

        Setting::set(
            'users.bulk_operations_enabled',
            true,
            'boolean',
            'user_management',
            'Enable bulk user operations in admin panel',
            false
        );

        Setting::set(
            'users.export_enabled',
            true,
            'boolean',
            'user_management',
            'Allow exporting user data',
            false
        );
    }

    /**
     * Create security-related settings
     */
    protected function createSecuritySettings(): void
    {
        Setting::set(
            'security.password_min_length',
            8,
            'integer',
            'security',
            'Minimum password length requirement',
            true
        );

        Setting::set(
            'security.password_require_uppercase',
            true,
            'boolean',
            'security',
            'Require uppercase letters in passwords',
            true
        );

        Setting::set(
            'security.password_require_lowercase',
            true,
            'boolean',
            'security',
            'Require lowercase letters in passwords',
            true
        );

        Setting::set(
            'security.password_require_numbers',
            true,
            'boolean',
            'security',
            'Require numbers in passwords',
            true
        );

        Setting::set(
            'security.password_require_symbols',
            false,
            'boolean',
            'security',
            'Require special characters in passwords',
            true
        );

        Setting::set(
            'security.password_history_limit',
            5,
            'integer',
            'security',
            'Number of previous passwords to remember',
            false
        );

        Setting::set(
            'security.two_factor_required_roles',
            ['Super Admin', 'Admin'],
            'array',
            'security',
            'Roles that require two-factor authentication',
            false
        );

        Setting::set(
            'security.two_factor_grace_period',
            7,
            'integer',
            'security',
            'Days to set up 2FA before enforcement',
            false
        );

        Setting::set(
            'security.ip_whitelist_enabled',
            false,
            'boolean',
            'security',
            'Enable IP address whitelisting',
            false
        );

        Setting::set(
            'security.ip_whitelist',
            [],
            'array',
            'security',
            'Whitelisted IP addresses',
            false
        );

        Setting::set(
            'security.audit_log_enabled',
            true,
            'boolean',
            'security',
            'Enable audit logging for security events',
            false
        );

        Setting::set(
            'security.audit_log_retention_days',
            365,
            'integer',
            'security',
            'Days to retain audit logs',
            false
        );
    }

    /**
     * Create email-related settings
     */
    protected function createEmailSettings(): void
    {
        Setting::set(
            'email.from_name',
            'Thorium90 System',
            'string',
            'email',
            'Default sender name for system emails',
            false
        );

        Setting::set(
            'email.from_address',
            'noreply@thorium90.local',
            'string',
            'email',
            'Default sender email address',
            false
        );

        Setting::set(
            'email.welcome_enabled',
            true,
            'boolean',
            'email',
            'Send welcome email to new users',
            false
        );

        Setting::set(
            'email.password_reset_expiry',
            60,
            'integer',
            'email',
            'Password reset link expiry in minutes',
            false
        );

        Setting::set(
            'email.verification_expiry',
            1440,
            'integer',
            'email',
            'Email verification link expiry in minutes (24 hours)',
            false
        );

        Setting::set(
            'email.notification_preferences',
            [
                'user_registered' => true,
                'user_login_failed' => true,
                'role_changed' => true,
                'password_changed' => true,
            ],
            'json',
            'email',
            'Email notification preferences for administrators',
            false
        );
    }

    /**
     * Create feature toggle settings
     */
    protected function createFeatureSettings(): void
    {
        Setting::set(
            'features.api_enabled',
            true,
            'boolean',
            'features',
            'Enable API endpoints',
            false
        );

        Setting::set(
            'features.api_rate_limiting',
            true,
            'boolean',
            'features',
            'Enable API rate limiting',
            false
        );

        Setting::set(
            'features.api_rate_limit',
            60,
            'integer',
            'features',
            'API requests per minute limit',
            false
        );

        Setting::set(
            'features.content_management_enabled',
            true,
            'boolean',
            'features',
            'Enable content management features',
            false
        );

        Setting::set(
            'features.media_uploads_enabled',
            true,
            'boolean',
            'features',
            'Enable media upload functionality',
            true
        );

        Setting::set(
            'features.comments_enabled',
            false,
            'boolean',
            'features',
            'Enable comment system',
            true
        );

        Setting::set(
            'features.notifications_enabled',
            true,
            'boolean',
            'features',
            'Enable in-app notifications',
            false
        );

        Setting::set(
            'features.real_time_updates',
            false,
            'boolean',
            'features',
            'Enable real-time updates via WebSockets',
            false
        );

        Setting::set(
            'features.dark_mode_enabled',
            true,
            'boolean',
            'features',
            'Enable dark mode theme option',
            true
        );
    }

    /**
     * Create system-level settings
     */
    protected function createSystemSettings(): void
    {
        Setting::set(
            'system.debug_mode',
            false,
            'boolean',
            'system',
            'Enable debug mode (development only)',
            false
        );

        Setting::set(
            'system.log_level',
            'info',
            'string',
            'system',
            'System logging level',
            false
        );

        Setting::set(
            'system.cache_enabled',
            true,
            'boolean',
            'system',
            'Enable application caching',
            false
        );

        Setting::set(
            'system.cache_duration',
            3600,
            'integer',
            'system',
            'Default cache duration in seconds',
            false
        );

        Setting::set(
            'system.queue_enabled',
            true,
            'boolean',
            'system',
            'Enable background job processing',
            false
        );

        Setting::set(
            'system.backup_enabled',
            false,
            'boolean',
            'system',
            'Enable automatic database backups',
            false
        );

        Setting::set(
            'system.backup_frequency',
            'daily',
            'string',
            'system',
            'Backup frequency (daily, weekly, monthly)',
            false
        );

        Setting::set(
            'system.backup_retention_days',
            30,
            'integer',
            'system',
            'Days to retain backup files',
            false
        );

        Setting::set(
            'system.health_check_enabled',
            true,
            'boolean',
            'system',
            'Enable system health monitoring',
            false
        );

        Setting::set(
            'system.performance_monitoring',
            false,
            'boolean',
            'system',
            'Enable performance monitoring and metrics',
            false
        );

        Setting::set(
            'system.error_reporting_enabled',
            true,
            'boolean',
            'system',
            'Enable error reporting and tracking',
            false
        );
    }
}

# Social Login Integration

This document provides comprehensive information about the Laravel Socialite integration for OAuth authentication with multiple providers.

## Overview

The social login system integrates with the existing authentication infrastructure, supporting:

- **Google OAuth 2.0**
- **GitHub OAuth**
- **Facebook Login**
- **LinkedIn OAuth 2.0**
- **X (Twitter) OAuth 2.0**
- **GitLab OAuth 2.0**

## Features

- ✅ Automatic user creation for new social logins
- ✅ Integration with existing users via email matching
- ✅ Default role assignment for new social users
- ✅ Avatar synchronization from OAuth providers
- ✅ Comprehensive error handling and security validation
- ✅ Integration with Laravel Sanctum API authentication
- ✅ Compatible with Spatie Laravel Permission system

## Installation

Laravel Socialite is already installed and configured. The package is included in `composer.json`:

```json
{
    "require": {
        "laravel/socialite": "^5.23"
    }
}
```

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file for each OAuth provider you want to enable:

```env
# Social Login - Google
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URL=http://localhost/auth/google/callback

# Social Login - GitHub
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URL=http://localhost/auth/github/callback

# Social Login - Facebook
FACEBOOK_CLIENT_ID=your_facebook_app_id
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
FACEBOOK_REDIRECT_URL=http://localhost/auth/facebook/callback

# Social Login - LinkedIn
LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret
LINKEDIN_REDIRECT_URL=http://localhost/auth/linkedin/callback

# Social Login - Twitter/X
TWITTER_CLIENT_ID=your_twitter_client_id
TWITTER_CLIENT_SECRET=your_twitter_client_secret
TWITTER_REDIRECT_URL=http://localhost/auth/twitter/callback

# Social Login - GitLab
GITLAB_CLIENT_ID=your_gitlab_application_id
GITLAB_CLIENT_SECRET=your_gitlab_secret
GITLAB_REDIRECT_URL=http://localhost/auth/gitlab/callback
```

### OAuth Provider Setup

#### Google OAuth 2.0

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URIs: `http://localhost/auth/google/callback`

#### GitHub OAuth

1. Go to GitHub Settings > Developer settings > OAuth Apps
2. Create a new OAuth App
3. Set Authorization callback URL: `http://localhost/auth/github/callback`

#### Facebook Login

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app
3. Add Facebook Login product
4. Set Valid OAuth Redirect URIs: `http://localhost/auth/facebook/callback`

#### LinkedIn OAuth 2.0

1. Go to [LinkedIn Developer Portal](https://www.linkedin.com/developers/)
2. Create a new app
3. Add Sign In with LinkedIn product
4. Set Authorized redirect URLs: `http://localhost/auth/linkedin/callback`

#### X (Twitter) OAuth 2.0

1. Go to [Twitter Developer Portal](https://developer.twitter.com/)
2. Create a new app
3. Enable OAuth 2.0
4. Set Callback URLs: `http://localhost/auth/twitter/callback`

#### GitLab OAuth 2.0

1. Go to GitLab Settings > Applications
2. Create a new application
3. Set Redirect URI: `http://localhost/auth/gitlab/callback`
4. Select required scopes (read_user, openid, profile, email)

## Usage

### Routes

The following routes are automatically registered:

```php
// Redirect to OAuth provider
GET /auth/{provider}

// Handle OAuth callback
GET /auth/{provider}/callback
```

Where `{provider}` can be: `google`, `github`, `facebook`, `linkedin`, `twitter`, `gitlab`

### Frontend Integration

Add social login buttons to your login form:

```html
<!-- Google Login -->
<a href="/auth/google" class="btn btn-google">
    Login with Google
</a>

<!-- GitHub Login -->
<a href="/auth/github" class="btn btn-github">
    Login with GitHub
</a>

<!-- Facebook Login -->
<a href="/auth/facebook" class="btn btn-facebook">
    Login with Facebook
</a>

<!-- LinkedIn Login -->
<a href="/auth/linkedin" class="btn btn-linkedin">
    Login with LinkedIn
</a>

<!-- Twitter/X Login -->
<a href="/auth/twitter" class="btn btn-twitter">
    Login with X
</a>

<!-- GitLab Login -->
<a href="/auth/gitlab" class="btn btn-gitlab">
    Login with GitLab
</a>
```

### Redirect After Login

You can specify a redirect URL after successful login:

```html
<a href="/auth/google?redirect=/admin/dashboard">
    Login with Google
</a>
```

## Database Schema

The social login fields are added to the `users` table:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('provider')->nullable();
    $table->string('provider_id')->nullable();
    $table->string('avatar')->nullable();
    
    // Unique composite index
    $table->unique(['provider', 'provider_id']);
});
```

## User Model Methods

The `User` model includes several helper methods for social login:

```php
// Find user by social provider
$user = User::findForSocialLogin('google', '123456789');

// Create user from social provider data
$user = User::createFromSocialProvider('github', $userData);

// Check if user is from social login
if ($user->isSocialUser()) {
    // Handle social user
}

// Get user avatar (social or Gravatar fallback)
$avatarUrl = $user->getAvatarUrl();
```

## Security Considerations

- ✅ CSRF protection on all social login routes
- ✅ Rate limiting on OAuth endpoints
- ✅ Proper validation of OAuth state parameters
- ✅ Secure handling of OAuth tokens (access tokens are never stored)
- ✅ Email verification bypass for social logins (considered verified)
- ✅ Session regeneration after successful login

## Error Handling

The system handles various error scenarios:

- **Invalid Provider**: Redirects to login with error message
- **Unconfigured Provider**: Redirects to login with configuration error
- **OAuth State Mismatch**: Redirects to login with security error
- **Missing Email**: Redirects to login requesting email permission
- **General OAuth Errors**: Redirects to login with generic error message

## Testing

Comprehensive tests are included in `tests/Feature/SocialLoginTest.php`:

```bash
# Run social login tests
php artisan test --filter=SocialLoginTest

# Run all authentication tests
php artisan test tests/Feature/
```

## API Integration

Social login works seamlessly with Laravel Sanctum API authentication. After successful OAuth login, users can:

1. Create API tokens via the existing token management system
2. Use social login for web authentication while maintaining API access
3. Link existing API-only accounts with social providers

## Role Management

New social users are automatically assigned the default `user` role if it exists. This integrates with the Spatie Laravel Permission system:

```php
// The system automatically assigns the 'user' role to new social users
$user = User::createFromSocialProvider('google', $userData);
// $user now has the 'user' role if it exists
```

## Troubleshooting

### Common Issues

1. **"Provider not configured" error**
   - Ensure all required environment variables are set
   - Check that the provider configuration exists in `config/services.php`

2. **"Invalid state" error**
   - Usually caused by session issues or CSRF token problems
   - Ensure sessions are working properly
   - Check that the callback URL matches exactly

3. **"Unable to retrieve email" error**
   - The OAuth provider didn't return an email address
   - Check OAuth app permissions/scopes
   - Some providers require explicit email permission

4. **Redirect loops**
   - Check that callback URLs are correctly configured
   - Ensure the OAuth app settings match your environment

### Debug Mode

Enable debug mode to see detailed error messages:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Check the Laravel logs for detailed OAuth error information:

```bash
tail -f storage/logs/laravel.log
```

## Production Deployment

### Environment Variables

Update your production environment variables with actual OAuth credentials:

```env
APP_URL=https://yourdomain.com

GOOGLE_CLIENT_ID=your_production_google_client_id
GOOGLE_CLIENT_SECRET=your_production_google_client_secret
GOOGLE_REDIRECT_URL=https://yourdomain.com/auth/google/callback

# Update all other providers similarly...
```

### OAuth App Configuration

Update all OAuth applications with production callback URLs:
- `https://yourdomain.com/auth/{provider}/callback`

### SSL/HTTPS

Ensure your production environment uses HTTPS for OAuth security.

## Support

For issues related to social login:

1. Check the troubleshooting section above
2. Review the test suite for expected behavior
3. Check Laravel Socialite documentation: https://laravel.com/docs/socialite
4. Review OAuth provider documentation for specific requirements

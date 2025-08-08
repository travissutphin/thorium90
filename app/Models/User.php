<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 * 
 * This is the core user model that integrates with the Multi-Role User Authentication system
 * and Laravel Sanctum API authentication. It extends Laravel's Authenticatable class and uses
 * both the HasRoles trait from Spatie Laravel Permission and HasApiTokens from Laravel Sanctum.
 * 
 * Key Features:
 * - Role and permission management via Spatie Laravel Permission
 * - API token authentication via Laravel Sanctum
 * - Soft deletes for data integrity and recovery
 * - Factory support for testing
 * - Email verification support (optional)
 * - Password hashing and remember token functionality
 * 
 * Available Methods (from HasRoles trait):
 * - hasRole($role): Check if user has a specific role
 * - hasAnyRole($roles): Check if user has any of the specified roles
 * - hasAllRoles($roles): Check if user has all specified roles
 * - hasPermissionTo($permission): Check if user has a specific permission
 * - hasAnyPermission($permissions): Check if user has any of the specified permissions
 * - getAllPermissions(): Get all permissions (direct + inherited from roles)
 * - assignRole($role): Assign a role to the user
 * - removeRole($role): Remove a role from the user
 * - syncRoles($roles): Replace all user roles with the specified ones
 * 
 * Available Methods (from HasApiTokens trait):
 * - createToken($name, $abilities = ['*']): Create a new personal access token
 * - tokens(): Get all personal access tokens for the user
 * - currentAccessToken(): Get the current access token being used
 * - withAccessToken($accessToken): Set the current access token
 * 
 * @see https://spatie.be/docs/laravel-permission
 * @see https://laravel.com/docs/sanctum
 * @see https://laravel.com/docs/authentication
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, TwoFactorAuthenticatable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * 
     * These fields can be filled using mass assignment methods like create() or fill().
     * Note: Role and permission assignments should be done using the dedicated methods
     * from the HasRoles trait rather than mass assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'avatar',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * 
     * These attributes will not be included when the model is converted to an array
     * or JSON. This is important for security, especially for sensitive data like
     * passwords and remember tokens.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     * 
     * This method defines how certain attributes should be cast when retrieved
     * from or stored to the database. For example, dates are cast to Carbon
     * instances and passwords are automatically hashed.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Find a user by their social provider and provider ID.
     *
     * @param string $provider The OAuth provider name (google, github, facebook, etc.)
     * @param string $providerId The unique ID from the OAuth provider
     * @return User|null
     */
    public static function findForSocialLogin(string $provider, string $providerId): ?User
    {
        return static::where('provider', $provider)
                    ->where('provider_id', $providerId)
                    ->first();
    }

    /**
     * Create a new user from social provider data.
     *
     * @param string $provider The OAuth provider name
     * @param array $userData The user data from the OAuth provider
     * @return User
     */
    public static function createFromSocialProvider(string $provider, array $userData): User
    {
        return static::create([
            'name' => $userData['name'] ?? $userData['nickname'] ?? 'Unknown',
            'email' => $userData['email'],
            'provider' => $provider,
            'provider_id' => $userData['id'],
            'avatar' => $userData['avatar'] ?? null,
            'password' => null, // Social users don't have passwords
            'email_verified_at' => now(), // Social logins are considered verified
        ]);
    }

    /**
     * Check if this user was created via social login.
     *
     * @return bool
     */
    public function isSocialUser(): bool
    {
        return !is_null($this->provider) && !is_null($this->provider_id);
    }

    /**
     * Get the user's avatar URL, preferring the social avatar if available.
     *
     * @param int $size The size of the avatar (for Gravatar fallback)
     * @return string
     */
    public function getAvatarUrl(int $size = 80): string
    {
        if ($this->avatar) {
            return $this->avatar;
        }

        // Fallback to Gravatar
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }
}

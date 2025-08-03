<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 * 
 * This is the core user model that integrates with the Multi-Role User Authentication system.
 * It extends Laravel's Authenticatable class and uses the HasRoles trait from the Spatie
 * Laravel Permission package to enable role-based access control (RBAC).
 * 
 * Key Features:
 * - Role and permission management via Spatie Laravel Permission
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
 * @see https://spatie.be/docs/laravel-permission
 * @see https://laravel.com/docs/authentication
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
}

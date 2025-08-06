<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * CreateNewUser Action for Laravel Fortify
 * 
 * This action handles user registration through Laravel Fortify while maintaining
 * compatibility with the existing Multi-Role User Authentication system. It automatically
 * assigns the 'Subscriber' role to new users and integrates with Spatie Laravel Permission.
 * 
 * Key Features:
 * - Validates user registration data
 * - Creates new users with proper password hashing
 * - Automatically assigns 'Subscriber' role to new users
 * - Maintains compatibility with existing authentication system
 * - Supports both regular and social login users
 * 
 * Integration Points:
 * - Uses existing User model with HasRoles trait
 * - Respects existing validation rules
 * - Maintains consistency with current user creation patterns
 * - Works alongside existing registration controllers
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     * @return \App\Models\User
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'email_verified_at' => null, // Will be set when email is verified
        ]);

        // Assign default 'Subscriber' role to new users
        // This maintains consistency with the existing role-based system
        $user->assignRole('Subscriber');

        return $user;
    }
}

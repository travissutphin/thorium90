<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

/**
 * ResetUserPassword Action for Laravel Fortify
 * 
 * This action handles password resets through Laravel Fortify while maintaining
 * compatibility with the existing Multi-Role User Authentication system. It provides
 * secure password reset functionality with enhanced validation.
 * 
 * Key Features:
 * - Validates password reset data
 * - Uses enhanced password validation rules
 * - Maintains compatibility with existing authentication system
 * - Preserves user roles and permissions during reset
 * - Works with existing email configuration (Resend)
 * 
 * Integration Points:
 * - Uses existing User model
 * - Respects existing password hashing methods
 * - Maintains consistency with current security practices
 * - Preserves role-based access control
 */
class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}

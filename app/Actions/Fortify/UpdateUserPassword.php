<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

/**
 * UpdateUserPassword Action for Laravel Fortify
 * 
 * This action handles password updates through Laravel Fortify while maintaining
 * compatibility with the existing Multi-Role User Authentication system. It provides
 * secure password validation and updating functionality.
 * 
 * Key Features:
 * - Validates current password before allowing updates
 * - Uses enhanced password validation rules
 * - Maintains compatibility with existing authentication system
 * - Supports users with and without existing passwords (social login users)
 * 
 * Integration Points:
 * - Uses existing User model
 * - Respects existing password hashing methods
 * - Maintains consistency with current security practices
 */
class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        $rules = [
            'password' => $this->passwordRules(),
        ];

        // Only require current password if user has a password set
        // This allows social login users to set their first password
        if ($user->password) {
            $rules['current_password'] = ['required', 'string', 'current_password'];
        }

        Validator::make($input, $rules, [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}

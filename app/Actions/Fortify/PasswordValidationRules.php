<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Rules\Password;

/**
 * PasswordValidationRules Trait for Laravel Fortify
 * 
 * This trait provides consistent password validation rules across all Fortify actions.
 * It maintains compatibility with the existing authentication system while providing
 * enhanced security through Laravel Fortify's password rules.
 * 
 * Key Features:
 * - Consistent password validation across all authentication actions
 * - Configurable password strength requirements
 * - Integration with Laravel Fortify's Password rule
 * - Maintains compatibility with existing password policies
 */
trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            new Password,
            'confirmed'
        ];
    }
}

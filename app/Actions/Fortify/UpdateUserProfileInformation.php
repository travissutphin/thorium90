<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

/**
 * UpdateUserProfileInformation Action for Laravel Fortify
 * 
 * This action handles user profile updates through Laravel Fortify while maintaining
 * compatibility with the existing Multi-Role User Authentication system. It respects
 * the existing user model structure and validation rules.
 * 
 * Key Features:
 * - Validates profile update data
 * - Handles email verification when email is changed
 * - Maintains compatibility with existing user model
 * - Preserves role and permission assignments
 * - Supports social login user profiles
 * 
 * Integration Points:
 * - Uses existing User model structure
 * - Respects existing validation patterns
 * - Maintains email verification workflow
 * - Preserves social login fields
 */
class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        // Check if email is being changed
        $emailChanged = $user->email !== $input['email'];

        // Update user information
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
        ]);

        // If email changed and user must verify email, reset verification
        if ($emailChanged && $user instanceof MustVerifyEmail) {
            $user->forceFill([
                'email_verified_at' => null,
            ]);
        }

        $user->save();
    }
}

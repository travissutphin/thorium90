<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('Super Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('role')->id ?? null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
                'regex:/^[a-zA-Z0-9\s\-_]+$/', // Allow alphanumeric, spaces, hyphens, underscores
            ],
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'string',
                'exists:permissions,name',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Role name is required.',
            'name.string' => 'Role name must be a valid string.',
            'name.max' => 'Role name cannot exceed 255 characters.',
            'name.unique' => 'A role with this name already exists.',
            'name.regex' => 'Role name can only contain letters, numbers, spaces, hyphens, and underscores.',
            'permissions.array' => 'Permissions must be provided as an array.',
            'permissions.*.string' => 'Each permission must be a valid string.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'role name',
            'permissions' => 'permissions',
            'permissions.*' => 'permission',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim and clean the role name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->input('name')),
            ]);
        }

        // Ensure permissions is an array and remove duplicates
        if ($this->has('permissions')) {
            $permissions = $this->input('permissions', []);
            if (is_array($permissions)) {
                $this->merge([
                    'permissions' => array_unique(array_filter($permissions)),
                ]);
            }
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $role = $this->route('role');
            $roleName = $this->input('name');
            $permissions = $this->input('permissions', []);

            // Prevent changing Super Admin role name
            if ($role && $role->name === 'Super Admin' && $roleName !== 'Super Admin') {
                $validator->errors()->add('name', 'Super Admin role name cannot be changed.');
            }

            // Additional validation: Check if trying to rename to reserved role names (except current)
            $reservedNames = ['Super Admin', 'Admin', 'Editor', 'Author', 'Subscriber'];
            if ($role && $role->name !== $roleName && in_array($roleName, $reservedNames)) {
                $validator->errors()->add('name', 'This role name is reserved and cannot be used.');
            }

            // Validate that at least one permission is selected
            if (empty($permissions)) {
                $validator->errors()->add('permissions', 'Please select at least one permission for this role.');
            }

            // Validate permission count (reasonable limit)
            if (count($permissions) > 50) {
                $validator->errors()->add('permissions', 'Too many permissions selected. Maximum of 50 permissions allowed.');
            }

            // Special validation for Super Admin role - ensure critical permissions remain
            if ($role && $role->name === 'Super Admin') {
                $criticalPermissions = [
                    'manage roles',
                    'manage permissions',
                    'view users',
                    'create users',
                    'edit users',
                    'delete users',
                    'manage user roles',
                ];

                $missingCritical = array_diff($criticalPermissions, $permissions);
                if (!empty($missingCritical)) {
                    $validator->errors()->add('permissions', 
                        'Super Admin role must retain critical permissions: ' . implode(', ', $missingCritical)
                    );
                }
            }

            // Prevent removing permissions if role has users and would break functionality
            if ($role && $role->users()->count() > 0) {
                $currentPermissions = $role->permissions->pluck('name')->toArray();
                $removedPermissions = array_diff($currentPermissions, $permissions);
                
                // Check for critical permissions being removed from roles with users
                $criticalForUsers = ['view dashboard'];
                $criticalRemoved = array_intersect($removedPermissions, $criticalForUsers);
                
                if (!empty($criticalRemoved)) {
                    $validator->errors()->add('permissions', 
                        'Cannot remove critical permissions (' . implode(', ', $criticalRemoved) . 
                        ') from a role that has assigned users.'
                    );
                }
            }
        });
    }

    /**
     * Get the validated data with additional processing.
     *
     * @return array
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Ensure permissions array exists even if empty
        if (!isset($validated['permissions'])) {
            $validated['permissions'] = [];
        }

        return $validated;
    }

    /**
     * Get the role being updated.
     *
     * @return Role|null
     */
    public function getRole(): ?Role
    {
        return $this->route('role');
    }

    /**
     * Check if the role name is being changed.
     *
     * @return bool
     */
    public function isNameChanging(): bool
    {
        $role = $this->getRole();
        return $role && $role->name !== $this->input('name');
    }

    /**
     * Get permissions being added.
     *
     * @return array
     */
    public function getAddedPermissions(): array
    {
        $role = $this->getRole();
        if (!$role) {
            return [];
        }

        $currentPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $this->input('permissions', []);

        return array_diff($newPermissions, $currentPermissions);
    }

    /**
     * Get permissions being removed.
     *
     * @return array
     */
    public function getRemovedPermissions(): array
    {
        $role = $this->getRole();
        if (!$role) {
            return [];
        }

        $currentPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $this->input('permissions', []);

        return array_diff($currentPermissions, $newPermissions);
    }
}

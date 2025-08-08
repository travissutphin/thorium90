<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

class StoreRoleRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name',
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
            // Additional validation: Check if trying to create reserved role names
            $reservedNames = ['Super Admin', 'Admin', 'Editor', 'Author', 'Subscriber'];
            $roleName = $this->input('name');
            
            if (in_array($roleName, $reservedNames)) {
                $validator->errors()->add('name', 'This role name is reserved and cannot be used.');
            }

            // Validate that at least one permission is selected if permissions are provided
            $permissions = $this->input('permissions', []);
            if (empty($permissions)) {
                $validator->errors()->add('permissions', 'Please select at least one permission for this role.');
            }

            // Validate permission count (reasonable limit)
            if (count($permissions) > 50) {
                $validator->errors()->add('permissions', 'Too many permissions selected. Maximum of 50 permissions allowed.');
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

        // Add guard_name for consistency
        $validated['guard_name'] = 'web';

        return $validated;
    }
}

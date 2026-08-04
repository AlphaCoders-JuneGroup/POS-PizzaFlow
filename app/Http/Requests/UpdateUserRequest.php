<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin, UserRole::StoreManager) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/\s+/', '', (string) $this->input('phone')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $roleOptions = $this->manageableRoleValues();
        $userId = $this->route('user');

        return [
            'name' => ['required', 'string', 'min:2', 'max:120', 'regex:/^[\pL\s\'\-\.]+$/u'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, '_id'),
            ],
            'phone' => ['required', 'string', 'regex:/^(\+94|0)?[1-9]\d{8}$/'],
            'role' => ['required', Rule::in($roleOptions)],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Enter a valid Sri Lankan number (e.g. 0771234567 or +94771234567).',
            'role.required' => 'Please select a role.',
            'role.in' => 'Selected role is not allowed.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }

    /**
     * @return list<string>
     */
    private function manageableRoleValues(): array
    {
        $roles = UserRole::cases();

        if ($this->user()?->hasRole(UserRole::StoreManager) && ! $this->user()?->isAdmin()) {
            $roles = array_values(array_filter(
                $roles,
                fn (UserRole $role) => $role !== UserRole::Admin
            ));
        }

        return array_map(fn (UserRole $role) => $role->value, $roles);
    }
}

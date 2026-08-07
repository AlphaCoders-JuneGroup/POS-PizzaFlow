<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreDriverRequest extends FormRequest
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
            'service_zone' => trim((string) $this->input('service_zone')),
            '_form' => 'add_driver',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120', 'regex:/^[\pL\s\'\-\.]+$/u'],
            'email' => ['required', 'string', 'email:filter', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^(\+94|0)?[1-9]\d{8}$/'],
            'service_zone' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\pL\s\'\-\.]+$/u'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
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
            'name.min' => 'Name must be at least 2 characters.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Enter a valid Sri Lankan number (e.g. 0771234567 or +94771234567).',
            'service_zone.required' => 'Service zone is required.',
            'service_zone.regex' => 'Service zone can only contain letters and spaces.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_zone' => 'service zone',
            'password_confirmation' => 'password confirmation',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('delivery.index', ['tab' => 'drivers']);
    }
}

<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class RegisterRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'business_name' => ['required', 'string', 'min:2', 'max:255'],
            'contact_phone' => ['required', 'string', 'regex:/^[0-9+\-\s()]+$/', 'min:10', 'max:20'],
            'business_address' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'business_name.required' => 'Business name is required',
            'business_name.min' => 'Business name must be at least 2 characters',
            'contact_phone.required' => 'Contact phone is required',
            'contact_phone.regex' => 'Please provide a valid phone number',
            'contact_phone.min' => 'Phone number must be at least 10 characters',
            'business_address.required' => 'Business address is required',
            'business_address.min' => 'Business address must be at least 5 characters',
        ];
    }
}
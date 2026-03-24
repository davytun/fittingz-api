<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class VerifyEmailRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'code'  => ['required', 'string', 'size:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email'    => 'Please provide a valid email address',
            'email.exists'   => 'No account found with this email address',
            'code.required'  => 'Verification code is required',
            'code.size'      => 'Verification code must be 4 digits',
        ];
    }
}

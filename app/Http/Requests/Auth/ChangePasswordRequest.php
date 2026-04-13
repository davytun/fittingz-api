<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class ChangePasswordRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password'          => ['required', 'string'],
            'new_password'              => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'          => 'Current password is required',
            'new_password.required'              => 'New password is required',
            'new_password.min'                   => 'New password must be at least 8 characters',
            'new_password.confirmed'             => 'New password confirmation does not match',
            'new_password_confirmation.required' => 'New password confirmation is required',
        ];
    }
}
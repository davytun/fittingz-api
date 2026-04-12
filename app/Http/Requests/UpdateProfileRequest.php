<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name'    => ['sometimes', 'string', 'min:2', 'max:255'],
            'contact_phone'    => ['sometimes', 'string', 'min:10', 'max:20'],
            'business_address' => ['sometimes', 'string', 'min:5', 'max:500'],
            'email'            => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'email_notifications' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_name.min'    => 'Business name must be at least 2 characters',
            'business_name.max'    => 'Business name may not exceed 255 characters',
            'contact_phone.min'    => 'Phone number must be at least 10 characters',
            'contact_phone.max'    => 'Phone number may not exceed 20 characters',
            'business_address.min' => 'Business address must be at least 5 characters',
            'business_address.max' => 'Business address may not exceed 500 characters',
            'email.email'          => 'Please provide a valid email address',
            'email.unique'         => 'This email address is already in use',
        ];
    }

    protected function prepareForValidation()
    {
        $merge = [];

        if ($this->has('business_name')) {
            $merge['business_name'] = trim($this->business_name);
        }

        if ($this->has('contact_phone')) {
            $merge['contact_phone'] = trim($this->contact_phone);
        }

        if ($this->has('business_address')) {
            $merge['business_address'] = trim($this->business_address);
        }

        if ($this->has('email')) {
            $merge['email'] = strtolower(trim($this->email));
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}

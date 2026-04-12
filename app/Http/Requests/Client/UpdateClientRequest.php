<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;
        $clientId = $this->route('client')?->getKey();

        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')
                    ->where('user_id', $userId)
                    ->ignore($clientId),
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^[0-9+\-\s()]+$/',
                'min:10',
                'max:20',
                Rule::unique('clients', 'phone')
                    ->where('user_id', $userId)
                    ->ignore($clientId),
            ],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Client name is required',
            'name.min' => 'Client name must be at least 2 characters',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'A client with this email already exists',
            'phone.regex' => 'Please provide a valid phone number',
            'phone.min' => 'Phone number must be at least 10 characters',
            'phone.unique' => 'A client with this phone number already exists',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender must be Male, Female, or Other',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => $this->email ? strtolower(trim($this->email)) : null,
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => $this->phone ? trim($this->phone) : null,
            ]);
        }

        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }
}

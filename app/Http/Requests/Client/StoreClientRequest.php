<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\BaseRequest;
use App\Rules\ClientContactRequired;
use Illuminate\Validation\Rule;

class StoreClientRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->where('user_id', $userId),
                new ClientContactRequired,
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^[0-9+\-\s()]+$/',
                'min:10',
                'max:20',
                Rule::unique('clients', 'phone')->where('user_id', $userId),
            ],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
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
        $this->merge([
            'email' => $this->email ? strtolower(trim($this->email)) : null,
            'phone' => $this->phone ? trim($this->phone) : null,
            'name' => trim($this->name),
        ]);
    }
}

<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'details'           => ['nullable', 'array'],
            'details.*'         => ['nullable', 'string', 'max:500'],
            'style_description' => ['nullable', 'string', 'max:2000'],
            'total_amount'      => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency'          => ['sometimes', Rule::in(['NGN', 'USD', 'GBP', 'EUR'])],
            'due_date'          => ['nullable', 'date'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'total_amount.required'        => 'Total amount is required',
            'total_amount.min'             => 'Total amount must be at least 0',
            'total_amount.max'             => 'Total amount is too large',
            'currency.in'                  => 'Currency must be one of: NGN, USD, GBP, EUR',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('style_description')) {
            $this->merge(['style_description' => $this->style_description ? trim($this->style_description) : null]);
        }

        if ($this->has('notes')) {
            $this->merge(['notes' => $this->notes ? trim($this->notes) : null]);
        }
    }
}
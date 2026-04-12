<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;
        $client = $this->route('client');

        return [
            'measurement_id'    => [
                'nullable',
                'uuid',
                Rule::exists('measurements', 'id')->where(function ($query) use ($userId, $client) {
                    $query->where('user_id', $userId);
                    if ($client) {
                        $query->where('client_id', $client->getKey());
                    }
                }),
            ],
            'details'           => ['nullable', 'array'],
            'details.*'         => ['nullable', 'string', 'max:500'],
            'style_description' => ['nullable', 'string', 'max:2000'],
            'total_amount'      => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency'          => ['sometimes', Rule::in(['NGN', 'USD', 'GBP', 'EUR'])],
            'status'            => ['sometimes', Rule::in(['pending_payment', 'in_progress', 'completed', 'delivered', 'cancelled'])],
            'due_date'          => ['nullable', 'date', 'after_or_equal:today'],
            'delivery_date'     => ['nullable', 'date', 'after_or_equal:due_date'],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'deposit'           => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'measurement_id.exists'        => 'Selected measurement not found',
            'total_amount.required'        => 'Total amount is required',
            'total_amount.min'             => 'Total amount must be at least 0',
            'total_amount.max'             => 'Total amount is too large',
            'currency.in'                  => 'Currency must be one of: NGN, USD, GBP, EUR',
            'due_date.after_or_equal'      => 'Due date cannot be in the past',
            'delivery_date.after_or_equal' => 'Delivery date must be on or after due date',
            'deposit.min'                  => 'Deposit must be at least 0',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'style_description' => $this->style_description ? trim($this->style_description) : null,
            'notes'             => $this->notes ? trim($this->notes) : null,
        ]);
    }
}

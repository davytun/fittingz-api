<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'pos', 'other'])],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required',
            'amount.min' => 'Payment amount must be at least 0.01',
            'amount.max' => 'Payment amount is too large',
            'payment_date.required' => 'Payment date is required',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method. Must be: cash, bank_transfer, pos, or other',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'reference' => $this->reference ? trim($this->reference) : null,
            'notes' => $this->notes ? trim($this->notes) : null,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->has('amount')) {
                $order = $this->route('order');
                
                if ($order) {
                    $balance = $order->balance;
                    
                    if ($this->amount > $balance) {
                        $validator->errors()->add(
                            'amount',
                            "Payment amount ({$this->amount}) exceeds outstanding balance ({$balance})"
                        );
                    }
                }
            }
        });
    }
}

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

        return [
            'client_id' => [
                'required',
                'uuid',
                Rule::exists('clients', 'id')->where('user_id', $userId),
            ],
            'measurement_id' => [
                'nullable',
                'uuid',
                Rule::exists('measurements', 'id')->where('user_id', $userId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:1'],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed', 'delivered', 'cancelled'])],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:due_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Client is required',
            'client_id.exists' => 'Selected client not found',
            'measurement_id.exists' => 'Selected measurement not found',
            'title.required' => 'Order title is required',
            'title.max' => 'Order title cannot exceed 255 characters',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'total_amount.required' => 'Total amount is required',
            'total_amount.min' => 'Total amount must be at least 0',
            'total_amount.max' => 'Total amount is too large',
            'due_date.after_or_equal' => 'Due date cannot be in the past',
            'delivery_date.after_or_equal' => 'Delivery date must be on or after due date',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => $this->title ? trim($this->title) : null,
            'description' => $this->description ? trim($this->description) : null,
            'notes' => $this->notes ? trim($this->notes) : null,
        ]);
    }
}
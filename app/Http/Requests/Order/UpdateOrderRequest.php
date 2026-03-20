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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'total_amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'due_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:due_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Order title is required',
            'title.max' => 'Order title cannot exceed 255 characters',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'total_amount.required' => 'Total amount is required',
            'total_amount.min' => 'Total amount must be at least 0',
            'total_amount.max' => 'Total amount is too large',
            'delivery_date.after_or_equal' => 'Delivery date must be on or after due date',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('title')) {
            $this->merge(['title' => trim($this->title)]);
        }

        if ($this->has('description')) {
            $this->merge(['description' => $this->description ? trim($this->description) : null]);
        }

        if ($this->has('notes')) {
            $this->merge(['notes' => $this->notes ? trim($this->notes) : null]);
        }
    }
}
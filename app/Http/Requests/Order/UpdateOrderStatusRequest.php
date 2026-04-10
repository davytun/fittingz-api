<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['pending_payment', 'in_progress', 'completed', 'delivered', 'cancelled']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status. Must be: pending_payment, in_progress, completed, delivered, or cancelled',
        ];
    }
}
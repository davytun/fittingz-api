<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateOrderMeasurementRequest extends BaseRequest
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
            'measurement_id' => [
                'required',
                'uuid',
                Rule::exists('measurements', 'id')->where(function ($query) use ($userId, $client) {
                    $query->where('user_id', $userId);

                    if ($client) {
                        $query->where('client_id', $client->getKey());
                    }
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'measurement_id.required' => 'Measurement is required',
            'measurement_id.uuid' => 'Invalid measurement ID',
            'measurement_id.exists' => 'Selected measurement not found',
        ];
    }
}

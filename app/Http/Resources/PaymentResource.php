<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'title' => $this->order->title,
                'client' => [
                    'id' => $this->order->client->id,
                    'name' => $this->order->client->name,
                ],
            ],
            'amount' => (float) $this->amount,
            'payment_date' => $this->payment_date->format('Y-m-d'),
            'payment_method' => $this->payment_method->value,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
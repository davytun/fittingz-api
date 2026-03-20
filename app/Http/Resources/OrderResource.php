<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\StyleResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ],
            'measurement' => $this->measurement ? [
                'id' => $this->measurement->id,
                'is_default' => $this->measurement->is_default,
            ] : null,
            'title' => $this->title,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'total_amount' => (float) $this->total_amount,
            'total_paid' => $this->total_paid,
            'balance' => $this->balance,
            'payment_status' => $this->payment_status,
            'payments_count' => $this->payments()->count(),
            'styles' => StyleResource::collection($this->whenLoaded('styles')),
            'status' => $this->status->value,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

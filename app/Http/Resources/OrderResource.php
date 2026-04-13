<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\StyleResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order_number'      => $this->order_number,
            'client'            => $this->whenLoaded('client', fn () => [
                'id'   => $this->client->id,
                'name' => $this->client->name,
            ]),
            'measurement'       => $this->whenLoaded('measurement', fn () => $this->measurement ? [
                'id'         => $this->measurement->id,
                'name'       => $this->measurement->name,
                'is_default' => $this->measurement->is_default,
            ] : null),
            'details'           => $this->details,
            'style_description' => $this->style_description,
            'total_amount'      => (float) $this->total_amount,
            'currency'          => $this->currency->value,
            'total_paid'        => array_key_exists('payments_sum_amount', $this->resource->getAttributes())
                                    ? (float) ($this->payments_sum_amount ?? 0)
                                    : $this->total_paid,
            'balance'           => array_key_exists('payments_sum_amount', $this->resource->getAttributes())
                                    ? (float) ($this->total_amount - ($this->payments_sum_amount ?? 0))
                                    : $this->balance,
            'payment_status'    => $this->payment_status,
            'payments_count'    => $this->when(
                isset($this->payments_count),
                $this->payments_count,
                fn () => $this->whenLoaded('payments', fn () => $this->payments->count())
            ),
            'payments'          => PaymentResource::collection($this->whenLoaded('payments')),
            'styles'            => StyleResource::collection($this->whenLoaded('styles')),
            'status'            => $this->status->value,
            'due_date'          => $this->due_date?->format('Y-m-d'),
            'notes'             => $this->notes,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}

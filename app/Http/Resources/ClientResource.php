<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender->value,
            'orders_count' => $this->when(isset($this->orders_count), $this->orders_count),
            'measurements_count' => $this->when(isset($this->measurements_count), $this->measurements_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
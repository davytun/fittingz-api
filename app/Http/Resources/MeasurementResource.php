<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeasurementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'client_id'        => $this->client_id,
            'name'             => $this->name,
            'fields'           => $this->fields,
            'unit'             => $this->unit?->value,
            'notes'            => $this->notes,
            'measurement_date' => $this->measurement_date?->format('Y-m-d'),
            'is_default'       => $this->is_default,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'business_name' => $this->business_name,
            'contact_phone' => $this->contact_phone,
            'business_address' => $this->business_address,
            'email_verified' => ! is_null($this->email_verified_at),
            'created_at' => $this->created_at,
        ];
    }
}

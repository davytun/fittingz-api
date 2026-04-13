<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StyleImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'imageUrl'    => $this->image_url,
            'publicId'    => $this->public_id,
            'category'    => $this->category,
            'description' => $this->description,
            'clientId'    => $this->client_id,
            'adminId'     => $this->admin_id,
            'createdAt'   => $this->created_at,
        ];
    }
}

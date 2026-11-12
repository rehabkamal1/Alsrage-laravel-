<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternalOfficeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'license_number' => $this->license_number,
            'country' => $this->country,
            'contacts' => $this->contacts,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'whatsapp_link' => $this->whatsapp_link,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
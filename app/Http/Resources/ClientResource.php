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
            'client_type' => $this->client_type,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee'),
            'phone' => $this->phone,
            'additional_phone' => $this->additional_phone,
            'city' => $this->city,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
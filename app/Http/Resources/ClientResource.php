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
            'category' => $this->category,
            'office_name' => $this->office_name,
            'phone' => $this->phone,
            'additional_phone' => $this->additional_phone,
            'address' => $this->address,
            'passport_number' => $this->passport_number,
            'national_id' => $this->national_id,
            'passport_image' => $this->passport_image ? asset('storage/' . $this->passport_image) : null,
            'visa_image' => $this->visa_image ? asset('storage/' . $this->visa_image) : null,
            'id_image' => $this->id_image ? asset('storage/' . $this->id_image) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
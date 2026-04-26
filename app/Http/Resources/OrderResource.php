<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->client),
            'saudi_office_id' => $this->saudi_office_id,
            'saudi_office' => new SaudiOfficeResource($this->saudiOffice),
            'external_office_id' => $this->external_office_id,
            'external_office' => new ExternalOfficeResource($this->externalOffice),
            'details' => $this->details,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

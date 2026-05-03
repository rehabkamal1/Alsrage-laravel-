<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order_number' => $this->order?->id,
            'saudi_office_name' => $this->order?->saudiOffice?->name,
            'visa_holder_name' => $this->order?->visa_holder_name ?? $this->order?->client?->visa_holder_name,
            'visa_number' => $this->order?->visa_number,
            'id_number' => $this->order?->id_number ?? $this->order?->client?->id_number,
            'passport_number' => $this->order?->passport_number ?? $this->order?->client?->passport_number,
            'sponsor_number' => $this->sponsor_number,
            'authorization_number' => $this->authorization_number,
            'is_authenticated' => $this->is_authenticated,
            'authentication_date' => $this->authentication_date,
            'certification_date' => $this->certification_date,
            'authentication_number' => $this->authentication_number,
            'last_action_date' => $this->last_action_date,
            'notes' => $this->notes,
            'priority_level' => $this->priority_level,
            'passport_status' => $this->passport_status,
            'transfer_status' => $this->transfer_status,
            'attachments' => AttachmentResource::collection($this->attachments),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'external_office_id' => $this->external_office_id,
            'external_office_name' => $this->externalOffice?->name,
            'external_office_country' => $this->externalOffice?->country,
        ];
    }
}

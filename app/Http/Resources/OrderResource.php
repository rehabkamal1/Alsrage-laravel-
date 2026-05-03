<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'visa_holder_name' => $this->visa_holder_name ?? $this->client?->visa_holder_name,
            'saudi_office_id' => $this->saudi_office_id,
            'saudi_office' => new SaudiOfficeResource($this->saudiOffice),
            'supplier_id' => $this->supplier_id,
            'supplier' => new SaudiOfficeResource($this->supplier),
            'external_office_id' => $this->external_office_id,
            'external_office' => new ExternalOfficeResource($this->externalOffice),
            'externalOffice' => new ExternalOfficeResource($this->externalOffice),
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->name,
            'employee' => $this->whenLoaded('employee'),
            'visa_number' => $this->visa_number,
            'id_number' => $this->id_number,
            'sponsor_number' => $this->sponsor_number,
            'passport_number' => $this->passport_number,
            'musaned_contract_number' => $this->musaned_contract_number,
            'authentication_contract_number' => $this->authentication_contract_number,
            'external_agent_number' => $this->external_agent_number,
            'contract_date' => $this->contract_date,
            'passport_date' => $this->passport_date,
            'total_price' => $this->total_price,
            'musaned_paid' => $this->musaned_paid,
            'price_difference' => $this->price_difference,
            'visa_image' => $this->visa_image,
            'contract_image' => $this->contract_image,
            'status' => $this->status,
            'notes' => $this->notes,
            'tracking' => new OrderTrackingResource($this->tracking),
            'transactions' => OrderTransactionResource::collection($this->transactions),
            'attachments' => AttachmentResource::collection($this->attachments),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

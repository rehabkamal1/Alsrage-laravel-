<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTrackingResource extends JsonResource
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
            'order_id' => $this->order_id,
            'is_authenticated' => $this->is_authenticated,
            'authentication_date' => $this->authentication_date,
            'authentication_number' => $this->authentication_number,
            'sent_to_external' => $this->sent_to_external,
            'external_status' => $this->external_status,
            'passport_filtered' => $this->passport_filtered,
            'is_delivered' => $this->is_delivered,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

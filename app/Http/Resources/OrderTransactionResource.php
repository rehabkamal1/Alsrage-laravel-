<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order_number' => $this->order?->id,
            'client_name' => $this->client?->name,
            'visa_holder_name' => $this->order?->client?->visa_holder_name,
            'type' => $this->type,
            'type_text' => $this->type === 'receipt' ? 'مقبوضات' : 'مصروفات',
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'bank_name' => $this->bank_name,
            'transfer_date' => $this->transfer_date,
            'transfer_number' => $this->transfer_number,
            'status' => $this->status,
            'priority_level' => $this->priority_level,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
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
            'order_ids' => $this->order_ids ?? [$this->order_id],
            'order_number' => $this->order?->id,
            'client_name' => $this->client?->name,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->name,
            'visa_holder_name' => $this->order?->client?->visa_holder_name,
            'type' => $this->type,
            'type_text' => $this->type === 'receipt' ? 'مقبوضات' : 'مصروفات',
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'bank_name' => $this->bank_name,
            'transfer_date' => $this->transfer_date,
            'transfer_number' => $this->transfer_number,
            'notes' => $this->notes,
            'is_reviewed' => $this->is_reviewed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
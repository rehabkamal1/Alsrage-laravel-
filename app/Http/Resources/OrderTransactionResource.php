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
            'type' => $this->type,
            'type_text' => $this->type === 'receipt' ? 'مقبوضات' : 'مصروفات',
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_method_text' => $this->payment_method === 'cash' ? 'كاش' : 'فيزا',
            'bank_name' => $this->bank_name,
            'transfer_date' => $this->transfer_date,
            'sender_name' => $this->sender_name,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getStatusText(): string
    {
        return match ($this->status) {
            'pending' => 'قيد الانتظار',
            'accepted' => 'مقبولة',
            'rejected' => 'مرفوضة',
            default => 'غير محدد',
        };
    }
}
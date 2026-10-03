<?php

namespace App\Http\Resources;

use App\Support\PermissionAccess;
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
            'phone' => PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_DELEGATE_NUMBERS)
                ? null
                : $this->phone,
            'additional_phone' => PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_DELEGATE_NUMBERS)
                ? null
                : $this->additional_phone,
            'city' => $this->city,
            'address' => $this->address,
            'orders' => OrderResource::collection($this->whenLoaded('orders')),
            'transactions' => PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_TRANSACTIONS)
                ? []
                : OrderTransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

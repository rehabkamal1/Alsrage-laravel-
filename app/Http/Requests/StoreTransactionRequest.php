<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:receipt,payment',
            'amount' => 'required|numeric|min:0.01',
            'order_id' => 'required|exists:orders,id',
            'client_id' => 'nullable|exists:clients,id',
            'payment_method' => 'nullable|in:cash,visa',
            'bank_name' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date',
            'sender_name' => 'nullable|string|max:255',
            'status' => 'sometimes|in:pending,accepted,rejected',
            'notes' => 'nullable|string',
        ];
    }
}
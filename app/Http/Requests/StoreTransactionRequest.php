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
            'payment_method' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date',
            'transfer_number' => 'nullable|string|max:100',
            'status' => 'nullable|string',
            'priority_level' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
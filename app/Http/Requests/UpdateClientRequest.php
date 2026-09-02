<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $client = $this->route('client');
        $clientId = $client instanceof \App\Models\Client ? $client->id : $client;

        return [
            'name' => 'nullable|string|max:255',
            'client_type' => 'sometimes|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('clients', 'phone')->ignore($clientId),
            ],
            'additional_phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }
}
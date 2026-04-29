<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'client_type' => 'sometimes|string|max:255',
            'employee_id' => 'sometimes|exists:employees,id',
            'phone' => 'sometimes|string|max:20|unique:clients,phone,' . ($this->client->id ?? $this->client),
            'additional_phone' => 'nullable|string|max:20',
            'city' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
        ];
    }
}
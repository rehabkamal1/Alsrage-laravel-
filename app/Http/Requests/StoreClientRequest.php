<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'client_type' => 'required|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'phone' => 'required|string|max:20|unique:clients,phone',
            'additional_phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }
}
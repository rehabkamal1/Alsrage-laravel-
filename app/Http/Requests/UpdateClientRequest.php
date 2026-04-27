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
            'category' => 'nullable|string|max:255',
            'office_name' => 'nullable|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:clients,phone,' . $this->client,
            'additional_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'passport_number' => 'nullable|string|max:50|unique:clients,passport_number,' . $this->client,
            'national_id' => 'nullable|string|max:50|unique:clients,national_id,' . $this->client,
            'passport_image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'visa_image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'id_image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
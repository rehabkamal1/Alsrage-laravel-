<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_office_id' => ['nullable', 'exists:external_offices,id'],
            'is_authenticated' => ['sometimes', 'boolean'],
            'authentication_date' => ['nullable', 'date'],
            'certification_date' => ['nullable', 'date'],
            'authentication_number' => ['nullable', 'string', 'max:100'],
            'authorization_number' => ['nullable', 'string', 'max:100'],
            'sponsor_number' => ['nullable', 'string', 'max:100'],
            'last_action_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'priority_level' => ['nullable', 'string'],
            'passport_status' => ['nullable', 'string'],
            'transfer_status' => ['nullable', 'string'],
        ];
    }
}

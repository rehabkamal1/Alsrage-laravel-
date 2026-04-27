<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderTrackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'is_authenticated' => ['sometimes', 'boolean'],
            'authentication_date' => ['nullable', 'date'],
            'authentication_number' => ['nullable', 'string', 'max:100'],
            'sent_to_external' => ['sometimes', 'boolean'],
            'external_status' => ['nullable', 'string', 'in:pending,accepted,rejected'],
            'passport_filtered' => ['nullable', 'string', 'in:pending,accepted,rejected'],
            'is_delivered' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMarketingLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_id' => 'nullable|integer',
            'source_type' => 'nullable|string|in:saudi_office,external_office,client',
            'type' => 'nullable|string|in:saudi_office,external_office,service_office',
            'notes' => 'nullable|string',
            'contact_date' => 'nullable|date',
            'next_followup_date' => 'nullable|date',
            'assigned_to' => 'nullable|string',
        ];
    }
}
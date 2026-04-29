<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarketingLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_id' => 'required|integer',
            'source_type' => 'required|string|in:saudi_office,external_office,client',
            'type' => 'required|string|in:saudi_office,external_office,service_office',
            'status' => 'nullable|string',
            'priority_level' => 'nullable|string',
            'notes' => 'nullable|string',
            'contact_date' => 'nullable|date',
            'next_followup_date' => 'nullable|date',
            'assigned_to' => 'nullable|string',
        ];
    }
}
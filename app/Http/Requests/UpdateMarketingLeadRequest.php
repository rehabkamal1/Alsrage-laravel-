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
            'status' => 'nullable|string',
            'priority_level' => 'nullable|string',
            'notes' => 'nullable|string',
            'contact_date' => 'nullable|date',
            'next_followup_date' => 'nullable|date',
            'assigned_to' => 'nullable|string',
        ];
    }
}

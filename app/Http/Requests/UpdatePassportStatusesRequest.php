<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePassportStatusesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statuses' => 'required|array',
            'statuses.*.key' => 'required|string',
            'statuses.*.label' => 'required|string',
            'statuses.*.color' => 'nullable|string',
            'statuses.*.sort_order' => 'nullable|integer',
            'statuses.*.is_active' => 'nullable|boolean',
        ];
    }
}
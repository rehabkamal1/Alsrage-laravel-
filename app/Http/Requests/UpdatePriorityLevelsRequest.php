<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriorityLevelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'levels' => 'required|array',
            'levels.*.key' => 'required|string',
            'levels.*.label' => 'required|string',
            'levels.*.color' => 'nullable|string',
            'levels.*.sort_order' => 'nullable|integer',
            'levels.*.is_active' => 'nullable|boolean',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'client_id' => ['required', 'exists:clients,id'],
            'saudi_office_id' => ['nullable', 'exists:saudi_offices,id'],
            'external_office_id' => ['nullable', 'exists:external_offices,id'],
            'details' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:pending,processing,done,canceled'],
        ];
    }
}

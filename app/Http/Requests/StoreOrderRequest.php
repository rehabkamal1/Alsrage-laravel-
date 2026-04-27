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
            'client_id' => ['nullable', 'exists:clients,id'],
            'new_client_name' => ['nullable', 'string', 'max:255', 'required_without:client_id'],
            'new_client_phone' => ['nullable', 'string', 'unique:clients,phone', 'required_without:client_id'],
            'saudi_office_id' => ['nullable', 'exists:saudi_offices,id'],
            'external_office_id' => ['nullable', 'exists:external_offices,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],

            'visa_number' => ['nullable', 'string', 'max:100'],
            'musaned_contract_number' => ['nullable', 'string', 'unique:orders,musaned_contract_number'],
            'authentication_contract_number' => ['nullable', 'string', 'unique:orders,authentication_contract_number'],
            'external_agent_number' => ['nullable', 'string', 'max:100'],
            'contract_date' => ['nullable', 'date'],
            'passport_date' => ['nullable', 'date'],

            'total_price' => ['nullable', 'numeric', 'min:0'],
            'musaned_paid' => ['nullable', 'numeric', 'min:0'],

            'visa_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'contract_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],

            'status' => ['sometimes', 'string', 'in:pending,in_progress,completed,cancelled'],
        ];
    }
}
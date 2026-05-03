<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:clients,id'],
            'visa_holder_name' => ['nullable', 'string', 'max:255'],
            'new_client_name' => ['nullable', 'string', 'max:255'],
            'new_client_phone' => ['nullable', 'string', 'unique:clients,phone'],
            'new_client_type' => ['nullable', 'string', 'in:individual,office'],
            'saudi_office_id' => ['required', 'exists:saudi_offices,id'],
            'supplier_id' => ['nullable', 'exists:saudi_offices,id'],
            'external_office_id' => ['nullable', 'exists:external_offices,id'],
            'visa_number' => ['required', 'string', 'max:100'],
            'nationality' => ['required', 'string', 'max:255'],
            'arrival_destination' => ['required', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:100'],
            'passport_number' => ['required', 'string', 'max:100'],
            'musaned_contract_number' => ['nullable', 'string', 'unique:orders,musaned_contract_number'],
            'authentication_contract_number' => ['nullable', 'string', 'max:255'],
            'external_agent_number' => ['nullable', 'string', 'max:255'],
            'contract_date' => ['nullable', 'date'],
            'passport_date' => ['nullable', 'date'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'musaned_paid' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'attachment_titles' => ['nullable', 'array'],
            'attachment_titles.*' => ['nullable', 'string', 'max:255'],
            'attachment_files' => ['nullable', 'array'],
            'attachment_files.*' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,pdf', 'max:5120'],
            'status' => ['sometimes', 'string', 'max:100'],
        ];
    }
}

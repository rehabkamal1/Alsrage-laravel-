<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'saudi_office_id' => ['nullable', 'exists:saudi_offices,id'],
            'supplier_id' => ['nullable', 'exists:saudi_offices,id'],
            'external_office_id' => ['nullable', 'exists:external_offices,id'],
            'visa_number' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'arrival_destination' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'passport_number' => ['nullable', 'string', 'max:100'],
            'musaned_contract_number' => ['nullable', 'string', 'unique:orders,musaned_contract_number,' . $this->route('order')],
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
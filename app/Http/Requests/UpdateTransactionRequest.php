<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use App\Support\PaymentMethodClassifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', 'in:receipt,payment'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'order_ids' => ['nullable', 'array'],
            'order_ids.*' => ['exists:orders,id'],
            'client_id' => ['sometimes', 'required', 'exists:clients,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'payment_method' => ['nullable', 'string'],
            'bank_name' => [
                Rule::requiredIf(function () {
                    $transaction = $this->route('transaction');
                    $paymentMethod = $this->input(
                        'payment_method',
                        $transaction instanceof Transaction ? $transaction->payment_method : null
                    );
                    $bankName = $this->input(
                        'bank_name',
                        $transaction instanceof Transaction ? $transaction->bank_name : null
                    );

                    return PaymentMethodClassifier::requiresBeneficiaryBank($paymentMethod)
                        && blank($bankName);
                }),
                'nullable',
                'string',
                'max:255',
            ],
            'transfer_date' => ['nullable', 'date'],
            'transfer_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_reviewed' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'نوع المعاملة مطلوب.',
            'type.in' => 'نوع المعاملة يجب أن يكون مقبوضات أو مصروفات.',

            'amount.required' => 'المبلغ مطلوب.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقمًا.',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر.',

            'client_id.required' => 'رقم المندوب مطلوب.',
            'client_id.exists' => 'المندوب المختار غير موجود.',

            'bank_name.required_if' => 'بنك المستفيد مطلوب عند اختيار طريقة دفع بنكية.',
            'bank_name.string' => 'بنك المستفيد يجب أن يكون نصًا.',
            'bank_name.max' => 'بنك المستفيد يجب ألا يزيد عن 255 حرفًا.',

            'order_id.exists' => 'الطلب المختار غير موجود.',
            'order_ids.array' => 'الطلبات يجب أن تكون قائمة.',
            'order_ids.*.exists' => 'أحد الطلبات المختارة غير موجود.',
            'employee_id.exists' => 'الموظف المختار غير موجود.',
            'payment_method.string' => 'طريقة الدفع يجب أن تكون نصًا.',
            'transfer_date.date' => 'تاريخ الحوالة غير صالح.',
            'transfer_number.string' => 'رقم الحوالة يجب أن يكون نصًا.',
            'transfer_number.max' => 'رقم الحوالة يجب ألا يزيد عن 100 حرف.',
            'notes.string' => 'الملاحظات يجب أن تكون نصًا.',
            'is_reviewed.boolean' => 'قيمة المراجعة يجب أن تكون صحيح أو خطأ.',
        ];
    }
}

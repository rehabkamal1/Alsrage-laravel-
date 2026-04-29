<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:jpeg,png,jpg,gif,pdf',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الصورة مطلوب',
            'file.required' => 'الملف مطلوب',
            'file.max' => 'حجم الملف لا يتجاوز 10 ميجابايت',
            'file.mimes' => 'نوع الملف غير مدعوم (jpg, png, gif, pdf)',
        ];
    }
}

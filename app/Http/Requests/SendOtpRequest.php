<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'], // البريد الإلكتروني أو رقم الهاتف
            'type' => ['required', 'in:email,phone'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'البريد الإلكتروني أو رقم الهاتف مطلوب',
            'type.required' => 'نوع التحقق مطلوب',
            'type.in' => 'نوع التحقق يجب أن يكون email أو phone',
        ];
    }
}

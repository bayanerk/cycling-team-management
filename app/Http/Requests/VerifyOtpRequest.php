<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
            'identifier' => ['required', 'string'],
            'type' => ['required', 'in:email,phone'],
            'otp_code' => ['required', 'string', 'size:6'],
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
            'otp_code.required' => 'رمز التحقق مطلوب',
            'otp_code.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
        ];
    }
}

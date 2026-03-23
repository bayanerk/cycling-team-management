<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
        $allowedRoles = ['coach', 'rider'];
        if (config('app.allow_admin_registration')) {
            $allowedRoles[] = 'admin';
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['nullable', 'in:male,female'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'birthday' => ['nullable', 'date'],
            'profession' => ['nullable', 'string', 'max:255'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'role' => ['nullable', Rule::in($allowedRoles)],
            'language' => ['nullable', 'in:ar,en'],
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
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
            'birthday.date' => 'تاريخ الميلاد يجب أن يكون تاريخًا صحيحًا (YYYY-MM-DD)',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 8 أحرف',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'role.in' => 'الدور غير مسموح. تسجيل حساب أدمن معطّل في هذا السيرفر (ضع ALLOW_ADMIN_REGISTRATION=true في .env إن كان مسموحاً).',
        ];
    }
}

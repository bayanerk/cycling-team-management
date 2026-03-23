<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectGroupRideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

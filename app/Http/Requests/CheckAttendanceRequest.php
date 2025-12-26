<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Admin check in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'participants' => ['required', 'array'],
            'participants.*.user_id' => ['required', 'exists:users,id'],
            'participants.*.status' => ['required', 'in:completed,no_show'],
            'participants.*.distance_km' => ['nullable', 'numeric', 'min:0'],
            'participants.*.avg_speed_kmh' => ['nullable', 'numeric', 'min:0'],
            'participants.*.calories_burned' => ['nullable', 'integer', 'min:0'],
            'participants.*.points_earned' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

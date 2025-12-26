<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkCompletedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authentication checked in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'avg_speed_kmh' => ['nullable', 'numeric', 'min:0'],
            'calories_burned' => ['nullable', 'integer', 'min:0'],
            'points_earned' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

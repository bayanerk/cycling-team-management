<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFitnessProfileRequest extends FormRequest
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
            'height_cm' => ['nullable', 'integer', 'min:50', 'max:250'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'other_sports' => ['nullable', 'string', 'max:500'],
            'last_ride_date' => ['nullable', 'date', 'before_or_equal:today'],
            'max_distance_km' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ];
    }
}

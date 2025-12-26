<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRideRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'level' => ['sometimes', 'in:Beginner,Intermediate,Advanced'],
            'distance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'start_time' => ['sometimes', 'date'],
            'gathering_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_time'],
            'image_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'break_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'start_lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'start_lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'end_lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'end_lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // سيتم التحقق من كونه أدمن في الـ middleware أو في controller
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
            'title' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'level' => ['required', 'in:Beginner,Intermediate,Advanced'],
            'distance' => ['nullable', 'numeric', 'min:0'],
            'start_time' => ['required', 'date'],
            'gathering_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'break_location' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'start_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'start_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'end_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'end_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTracksRequest extends FormRequest
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
            'tracks' => ['required', 'array', 'min:1'],
            'tracks.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'tracks.*.lng' => ['required', 'numeric', 'between:-180,180'],
            'tracks.*.speed' => ['required', 'numeric', 'min:0'],
            'tracks.*.recorded_at' => ['required', 'date'],
        ];
    }
}

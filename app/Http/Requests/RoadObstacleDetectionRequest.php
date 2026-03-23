<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoadObstacleDetectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            /**
             * Sensor window: each row must have exactly 8 floats (same order as HF Space).
             * Max 1000 rows (LSTM input); shorter windows are padded inside the Space).
             */
            'data' => ['required', 'array', 'min:1', 'max:1000'],
            'data.*' => ['array', 'size:8'],
            'data.*.*' => ['numeric'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\GroupRideRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupRideRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'group_name' => ['required', 'string', 'max:255'],
            'group_type' => [
                'required',
                'string',
                Rule::in(['friends', 'scouts', 'institute', 'association', 'organization', 'other']),
            ],
            'location' => ['required', 'string'],
            'scheduled_at' => ['required', 'date', $this->scheduledAtRule()],
            'people_count' => [
                'required',
                'integer',
                'min:'.GroupRideRequest::MIN_PEOPLE,
                'max:'.GroupRideRequest::MAX_PEOPLE,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'people_count.min' => 'الحد الأدنى لعدد الأشخاص هو '.GroupRideRequest::MIN_PEOPLE.' لقبول طلب رايد جماعي.',
            'people_count.max' => 'الحد الأقصى لعدد الأشخاص هو '.GroupRideRequest::MAX_PEOPLE.'.',
        ];
    }

    private function scheduledAtRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! $value) {
                return;
            }
            if (Carbon::parse($value)->lt(now()->addWeek())) {
                $fail('يجب أن يكون موعد الرايد بعد أسبوع كامل على الأقل من وقت تقديم الطلب.');
            }
        };
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\RequestType;
use App\Models\ScheduleRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ScheduleRequest::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([RequestType::Leave->value, RequestType::OnCall->value])],
            'reason' => ['required', 'string', 'max:1000'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}

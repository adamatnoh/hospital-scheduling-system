<?php

namespace App\Http\Requests;

use App\Models\ScheduleRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreSwapRequest extends FormRequest
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
            'assignment_id' => ['required', 'integer', 'exists:schedule_assignments,id'],
            'counterpart_assignment_id' => ['required', 'integer', 'exists:schedule_assignments,id', 'different:assignment_id'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}

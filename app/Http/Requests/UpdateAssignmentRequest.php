<?php

namespace App\Http\Requests;

use App\Models\ScheduleAssignment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');

        return $assignment instanceof ScheduleAssignment
            && ($this->user()?->can('update', $assignment) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'ward_id' => ['nullable', 'integer', 'exists:wards,id'],
            'shift' => ['nullable', 'string', 'in:Morning,Evening,Night'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\AssignmentType;
use App\Models\Department;
use App\Models\ScheduleRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $department = $this->route('department');

        return $department instanceof Department
            && ($this->user()?->can('generate', [ScheduleRun::class, $department]) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AssignmentType::class)],
            'year' => ['required', 'integer', 'min:2023', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}

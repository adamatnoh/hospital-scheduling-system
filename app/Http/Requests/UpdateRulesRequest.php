<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $department = $this->route('department');

        return $department instanceof Department
            && ($this->user()?->can('updateRules', $department) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'doctors_per_on_call_day' => ['required', 'integer', 'min:1', 'max:6'],
            'monthly_on_call_cap' => ['required', 'integer', 'min:1', 'max:31'],
            'rest_after_night' => ['required', 'boolean'],
            'include_weekends' => ['required', 'boolean'],
            'ward_block_days' => ['required', 'integer', 'min:1', 'max:28'],
            'holiday_state' => ['required', 'string', 'max:50'],
        ];
    }
}

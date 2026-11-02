<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->route('user');

        return $staff instanceof \App\Models\User
            && ($this->user()?->can('update', $staff) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $staff = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff?->id)],
            'role' => ['required', Rule::in([Role::Regular->value, Role::Scheduler->value])],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
        ];
    }
}

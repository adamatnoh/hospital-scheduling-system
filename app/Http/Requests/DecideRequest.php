<?php

namespace App\Http\Requests;

use App\Models\ScheduleRequest;
use Illuminate\Foundation\Http\FormRequest;

class DecideRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('scheduleRequest');

        return $application instanceof ScheduleRequest
            && ($this->user()?->can('decide', $application) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:1000'],
        ];
    }
}

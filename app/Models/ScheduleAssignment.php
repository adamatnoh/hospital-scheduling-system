<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAssignment extends Model
{
    protected $fillable = [
        'schedule_run_id',
        'type',
        'user_id',
        'department_id',
        'ward_id',
        'shift',
        'date',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssignmentType::class,
            'shift' => Shift::class,
            'date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ScheduleRun::class, 'schedule_run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function label(): string
    {
        $doctor = 'Dr '.$this->user->name;

        return match ($this->type) {
            AssignmentType::OnCall => $doctor.' · On Call',
            AssignmentType::Ward => $doctor.' · '.($this->ward->name ?? 'Ward'),
            AssignmentType::Shift => $doctor.' · '.$this->shift?->value,
        };
    }
}

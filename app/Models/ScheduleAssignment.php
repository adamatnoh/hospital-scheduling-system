<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property AssignmentType $type
 * @property Shift|null $shift
 * @property Carbon $date
 * @property int $user_id
 * @property int $department_id
 * @property int|null $ward_id
 * @property int $schedule_run_id
 * @property bool $is_published
 */
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

    /** @return BelongsTo<ScheduleRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(ScheduleRun::class, 'schedule_run_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Ward, $this> */
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

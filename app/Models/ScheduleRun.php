<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\RunStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property AssignmentType $type
 * @property RunStatus $status
 * @property int $department_id
 * @property int $year
 * @property int $month
 * @property array<string, mixed>|null $fairness
 * @property Carbon|null $published_at
 */
class ScheduleRun extends Model
{
    protected $fillable = [
        'department_id',
        'type',
        'year',
        'month',
        'status',
        'fairness',
        'generated_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssignmentType::class,
            'status' => RunStatus::class,
            'fairness' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<User, $this> */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /** @return HasMany<ScheduleAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }
}

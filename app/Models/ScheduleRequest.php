<?php

namespace App\Models;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property RequestType $type
 * @property RequestStatus $status
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property int $user_id
 * @property int $department_id
 * @property string $reason
 * @property string|null $rejection_reason
 */
class ScheduleRequest extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'rejection_reason',
        'reviewer_id',
        'reviewed_at',
        'assignment_id',
        'counterpart_user_id',
        'counterpart_assignment_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => RequestType::class,
            'status' => RequestStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
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

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return BelongsTo<ScheduleAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ScheduleAssignment::class, 'assignment_id');
    }

    /** @return BelongsTo<User, $this> */
    public function counterpart(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counterpart_user_id');
    }

    /** @return BelongsTo<ScheduleAssignment, $this> */
    public function counterpartAssignment(): BelongsTo
    {
        return $this->belongsTo(ScheduleAssignment::class, 'counterpart_assignment_id');
    }
}

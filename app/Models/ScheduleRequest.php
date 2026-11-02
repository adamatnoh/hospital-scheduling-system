<?php

namespace App\Models;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ScheduleAssignment::class, 'assignment_id');
    }

    public function counterpart(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counterpart_user_id');
    }

    public function counterpartAssignment(): BelongsTo
    {
        return $this->belongsTo(ScheduleAssignment::class, 'counterpart_assignment_id');
    }
}

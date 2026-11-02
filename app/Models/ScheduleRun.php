<?php

namespace App\Models;

use App\Enums\AssignmentType;
use App\Enums\RunStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }
}

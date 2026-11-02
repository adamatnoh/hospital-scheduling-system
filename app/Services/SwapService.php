<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SwapService
{
    public function __construct(private AuditRecorder $audit) {}

    public function request(User $user, int $assignmentId, int $counterpartAssignmentId, string $reason): ScheduleRequest
    {
        $mine = ScheduleAssignment::query()->where('is_published', true)->findOrFail($assignmentId);
        $theirs = ScheduleAssignment::query()->where('is_published', true)->findOrFail($counterpartAssignmentId);

        if ($mine->user_id !== $user->id || $mine->type !== $theirs->type || $mine->department_id !== $theirs->department_id) {
            throw ValidationException::withMessages([
                'assignment_id' => 'Choose one of your published duties and a colleague duty of the same kind.',
            ]);
        }

        if ($theirs->user_id === $user->id) {
            throw ValidationException::withMessages([
                'counterpart_assignment_id' => 'Pick a different doctor.',
            ]);
        }

        $request = ScheduleRequest::query()->create([
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'type' => RequestType::Swap,
            'start_date' => $mine->date,
            'end_date' => $theirs->date,
            'reason' => $reason,
            'status' => RequestStatus::Pending,
            'assignment_id' => $mine->id,
            'counterpart_user_id' => $theirs->user_id,
            'counterpart_assignment_id' => $theirs->id,
        ]);

        $this->audit->record('swap.requested', $request, [
            'assignment_id' => $mine->id,
            'counterpart_assignment_id' => $theirs->id,
        ], $user->department_id);

        return $request;
    }

    public function approve(ScheduleRequest $request, User $reviewer): void
    {
        DB::transaction(function () use ($request, $reviewer) {
            $mine = $request->assignment()->lockForUpdate()->first();
            $theirs = $request->counterpartAssignment()->lockForUpdate()->first();

            if ($mine === null || $theirs === null) {
                throw ValidationException::withMessages([
                    'decision' => 'Those duties are no longer on the published roster.',
                ]);
            }

            $clash = ScheduleAssignment::query()
                ->where('type', $mine->type)
                ->where('is_published', true)
                ->where(function ($query) use ($mine, $theirs) {
                    $query->where(fn ($inner) => $inner->where('user_id', $theirs->user_id)->whereDate('date', $mine->date))
                        ->orWhere(fn ($inner) => $inner->where('user_id', $mine->user_id)->whereDate('date', $theirs->date));
                })
                ->whereNotIn('id', [$mine->id, $theirs->id])
                ->exists();

            if ($clash) {
                throw ValidationException::withMessages([
                    'decision' => 'The swap would double-book one of the doctors.',
                ]);
            }

            $userA = $mine->user_id;
            $mine->update(['user_id' => $theirs->user_id]);
            $theirs->update(['user_id' => $userA]);

            $request->update([
                'status' => RequestStatus::Approved,
                'reviewer_id' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
        });
    }
}

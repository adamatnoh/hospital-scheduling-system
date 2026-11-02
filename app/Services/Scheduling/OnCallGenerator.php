<?php

namespace App\Services\Scheduling;

use App\Enums\AssignmentType;

/**
 * Two doctors a day, approved on-call requests first, then the doctors with
 * the fewest duties. The monthly cap is counted per doctor. The 2023 generator
 * counted assignments on the current day and, on days that already had a
 * request, inserted a stale doctor id.
 */
class OnCallGenerator
{
    use GeneratesRosters;

    protected function type(): AssignmentType
    {
        return AssignmentType::OnCall;
    }

    protected function build(RosterContext $context): array
    {
        $cap = max(1, (int) $context->department->monthly_on_call_cap);
        $perDay = max(1, (int) $context->department->doctors_per_on_call_day);
        $counts = [];
        $reserved = [];
        $skipped = [];

        foreach ($context->onCallRequests as $date => $userIds) {
            foreach ($userIds as $userId) {
                if ($context->onLeave($userId, $date)) {
                    $skipped[] = ['user_id' => $userId, 'date' => $date, 'why' => 'leave'];

                    continue;
                }
                if (($counts[$userId] ?? 0) >= $cap) {
                    $skipped[] = ['user_id' => $userId, 'date' => $date, 'why' => 'cap'];

                    continue;
                }
                if (in_array($userId, $reserved[$date] ?? [], true)) {
                    continue;
                }
                if (count($reserved[$date] ?? []) >= $perDay) {
                    $skipped[] = ['user_id' => $userId, 'date' => $date, 'why' => 'day full'];

                    continue;
                }
                $reserved[$date][] = $userId;
                $counts[$userId] = ($counts[$userId] ?? 0) + 1;
            }
        }

        $rows = [];
        $gaps = [];

        foreach ($context->dates() as $day) {
            $key = $day->toDateString();
            $assigned = $reserved[$key] ?? [];

            while (count($assigned) < $perDay) {
                $next = $context->doctors
                    ->filter(function ($doctor) use ($assigned, $counts, $cap, $context, $key) {
                        return ! in_array($doctor->id, $assigned, true)
                            && ! $context->onLeave($doctor->id, $key)
                            && ($counts[$doctor->id] ?? 0) < $cap;
                    })
                    ->sort(function ($left, $right) use ($counts) {
                        $load = ($counts[$left->id] ?? 0) <=> ($counts[$right->id] ?? 0);

                        return $load !== 0 ? $load : $left->id <=> $right->id;
                    })
                    ->first();

                if ($next === null) {
                    break;
                }

                $assigned[] = $next->id;
                $counts[$next->id] = ($counts[$next->id] ?? 0) + 1;
            }

            if (count($assigned) < $perDay) {
                $gaps[] = [
                    'date' => $key,
                    'assigned' => count($assigned),
                    'needed' => $perDay,
                    'holiday' => $context->holidays[$key] ?? null,
                ];
            }

            foreach ($assigned as $userId) {
                $rows[] = $this->row($userId, $key, null, null);
            }
        }

        return [
            'rows' => $rows,
            'fairness' => $this->fairness($context, $rows, $gaps, $skipped, [
                'On-call still runs on public holidays and weekends. Requests lose to approved leave, and ties go to the doctor with the smaller id.',
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function row(int $userId, string $date, ?int $wardId, ?string $shift): array
    {
        return [
            'type' => AssignmentType::OnCall->value,
            'user_id' => $userId,
            'ward_id' => $wardId,
            'shift' => $shift,
            'date' => $date,
        ];
    }
}

<?php

namespace App\Services\Scheduling;

use App\Enums\AssignmentType;

/**
 * Each doctor walks the month in blocks (14 days by default) and the ward
 * index advances globally, which is the 2023 round-robin. The next block
 * starts the day after the previous block ends. The original code set the
 * cursor to the end date itself, so every block overlapped by one day.
 * Leave is matched on the doctor id across the whole date range.
 */
class WardRotationGenerator
{
    use GeneratesRosters;

    protected function type(): AssignmentType
    {
        return AssignmentType::Ward;
    }

    protected function build(RosterContext $context): array
    {
        $wards = $context->department->wards()->orderBy('id')->get();
        $rows = [];
        $notes = [];

        if ($wards->isEmpty()) {
            return [
                'rows' => [],
                'fairness' => $this->fairness($context, [], [], [], [
                    'This department has no wards, so the rotation produced nothing.',
                ]),
            ];
        }

        $blockDays = max(1, (int) $context->department->ward_block_days);
        $wardIndex = 0;

        foreach ($context->doctors as $doctor) {
            $cursor = $context->start;
            while ($cursor->lte($context->end)) {
                $key = $cursor->toDateString();
                if ($context->onLeave($doctor->id, $key)) {
                    $cursor = $cursor->addDay();

                    continue;
                }

                $blockEnd = $cursor->addDays($blockDays - 1);
                if ($blockEnd->gt($context->end)) {
                    $blockEnd = $context->end;
                }

                $ward = $wards[$wardIndex % $wards->count()];
                $wardIndex++;

                for ($day = $cursor; $day->lte($blockEnd); $day = $day->addDay()) {
                    $dayKey = $day->toDateString();
                    if ($context->onLeave($doctor->id, $dayKey)) {
                        continue;
                    }
                    $rows[] = [
                        'type' => AssignmentType::Ward->value,
                        'user_id' => $doctor->id,
                        'ward_id' => $ward->id,
                        'shift' => null,
                        'date' => $dayKey,
                    ];
                }

                $cursor = $blockEnd->addDay();
            }
        }

        if ($rows === []) {
            $notes[] = 'No ward days were assigned. Every doctor may be on leave.';
        }

        return [
            'rows' => $rows,
            'fairness' => $this->fairness($context, $rows, [], [], $notes),
        ];
    }
}

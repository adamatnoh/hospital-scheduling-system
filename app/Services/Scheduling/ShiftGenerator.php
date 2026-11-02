<?php

namespace App\Services\Scheduling;

use App\Enums\AssignmentType;
use App\Enums\Shift;

/**
 * Weekday Morning, Evening and Night duties, skipping approved leave and
 * public holidays. Each day fills the shift that still has nobody, then the
 * shift the department has used least this month. Morning wins a tie, then
 * Evening, then Night. The 2023 code compared leave by name and only on the
 * start date, and its doctor-then-day loop gave everyone the same shift.
 */
class ShiftGenerator
{
    use GeneratesRosters;

    protected function type(): AssignmentType
    {
        return AssignmentType::Shift;
    }

    protected function build(RosterContext $context): array
    {
        $counts = [
            Shift::Morning->value => 0,
            Shift::Evening->value => 0,
            Shift::Night->value => 0,
        ];
        $loads = [];
        $previous = [];
        $rows = [];
        $rest = (bool) $context->department->rest_after_night;
        $weekends = (bool) $context->department->include_weekends;

        foreach ($context->dates() as $day) {
            $key = $day->toDateString();
            if (! $weekends && $day->isWeekend()) {
                continue;
            }
            if (isset($context->holidays[$key])) {
                continue;
            }

            $available = $context->doctors->filter(function ($doctor) use ($context, $key, $rest, $previous) {
                if ($context->onLeave($doctor->id, $key)) {
                    return false;
                }
                if ($rest && ($previous[$doctor->id] ?? null) === Shift::Night->value) {
                    return false;
                }

                return true;
            })->sort(function ($left, $right) use ($loads) {
                $load = ($loads[$left->id] ?? 0) <=> ($loads[$right->id] ?? 0);

                return $load !== 0 ? $load : $left->id <=> $right->id;
            })->values();

            $today = [
                Shift::Morning->value => 0,
                Shift::Evening->value => 0,
                Shift::Night->value => 0,
            ];

            foreach ($available as $doctor) {
                $shift = $this->leastFilled($today, $counts);
                $today[$shift]++;
                $counts[$shift]++;
                $loads[$doctor->id] = ($loads[$doctor->id] ?? 0) + 1;
                $previous[$doctor->id] = $shift;
                $rows[] = [
                    'type' => AssignmentType::Shift->value,
                    'user_id' => $doctor->id,
                    'ward_id' => null,
                    'shift' => $shift,
                    'date' => $key,
                ];
            }

            foreach ($context->doctors as $doctor) {
                if ($rest && ($previous[$doctor->id] ?? null) === Shift::Night->value && ! $available->contains('id', $doctor->id)) {
                    $previous[$doctor->id] = null;
                }
            }
        }

        $gaps = [];
        foreach ($context->dates() as $day) {
            $key = $day->toDateString();
            if ((! $weekends && $day->isWeekend()) || isset($context->holidays[$key])) {
                continue;
            }
            $covered = array_column(array_filter($rows, fn ($row) => $row['date'] === $key), 'shift');
            foreach (Shift::cases() as $shift) {
                if (! in_array($shift->value, $covered, true)) {
                    $gaps[] = ['date' => $key, 'shift' => $shift->value];
                }
            }
        }

        return [
            'rows' => $rows,
            'fairness' => $this->fairness($context, $rows, $gaps, [], [
                'Each weekday covers Morning, Evening and Night when enough doctors are free. The 2023 loop often gave the whole department the same shift on a day.',
                $rest
                    ? 'A night shift is followed by a rest day.'
                    : 'Rest after a night shift is off, matching the 2023 default.',
            ]),
        ];
    }

    /**
     * Cover the shift that is empty today before adding to a shift the
     * department has already used less often this month.
     *
     * @param  array<string, int>  $today
     * @param  array<string, int>  $month
     */
    private function leastFilled(array $today, array $month): string
    {
        $best = Shift::Morning->value;
        $bestToday = PHP_INT_MAX;
        $bestMonth = PHP_INT_MAX;

        foreach ([Shift::Morning->value, Shift::Evening->value, Shift::Night->value] as $shift) {
            $dayCount = $today[$shift];
            $monthCount = $month[$shift];
            if ($dayCount < $bestToday || ($dayCount === $bestToday && $monthCount < $bestMonth)) {
                $best = $shift;
                $bestToday = $dayCount;
                $bestMonth = $monthCount;
            }
        }

        return $best;
    }
}

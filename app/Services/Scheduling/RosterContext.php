<?php

namespace App\Services\Scheduling;

use App\Models\Department;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class RosterContext
{
    /**
     * @param  Collection<int, User>  $doctors
     * @param  array<int, array<string, true>>  $leave
     * @param  array<string, list<int>>  $onCallRequests
     * @param  array<string, string>  $holidays
     */
    public function __construct(
        public Department $department,
        public int $year,
        public int $month,
        public CarbonImmutable $start,
        public CarbonImmutable $end,
        public Collection $doctors,
        public array $leave,
        public array $onCallRequests,
        public array $holidays,
    ) {}

    /**
     * @return list<CarbonImmutable>
     */
    public function dates(): array
    {
        $dates = [];
        for ($day = $this->start; $day->lte($this->end); $day = $day->addDay()) {
            $dates[] = $day;
        }

        return $dates;
    }

    public function onLeave(int $userId, string $date): bool
    {
        return isset($this->leave[$userId][$date]);
    }
}

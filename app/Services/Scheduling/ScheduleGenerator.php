<?php

namespace App\Services\Scheduling;

use App\Enums\AssignmentType;
use App\Models\Department;
use App\Models\ScheduleRun;

class ScheduleGenerator
{
    public function __construct(
        private OnCallGenerator $onCall,
        private WardRotationGenerator $wards,
        private ShiftGenerator $shifts,
    ) {}

    public function preview(Department $department, AssignmentType $type, int $year, int $month, ?int $generatedBy = null): ScheduleRun
    {
        return match ($type) {
            AssignmentType::OnCall => $this->onCall->preview($department, $year, $month, $generatedBy),
            AssignmentType::Ward => $this->wards->preview($department, $year, $month, $generatedBy),
            AssignmentType::Shift => $this->shifts->preview($department, $year, $month, $generatedBy),
        };
    }
}

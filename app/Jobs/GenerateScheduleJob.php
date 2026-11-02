<?php

namespace App\Jobs;

use App\Enums\AssignmentType;
use App\Models\Department;
use App\Services\Scheduling\ScheduleGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateScheduleJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $departmentId,
        public string $type,
        public int $year,
        public int $month,
        public ?int $generatedBy = null,
    ) {}

    public function handle(ScheduleGenerator $generator): void
    {
        $department = Department::query()->findOrFail($this->departmentId);

        $generator->preview(
            $department,
            AssignmentType::from($this->type),
            $this->year,
            $this->month,
            $this->generatedBy,
        );
    }
}

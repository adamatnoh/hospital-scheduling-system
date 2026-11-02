<?php

namespace App\Services\Scheduling;

use App\Enums\AssignmentType;
use App\Enums\RunStatus;
use App\Models\Department;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRun;
use Illuminate\Support\Facades\DB;

trait GeneratesRosters
{
    public function __construct(protected RosterContextFactory $contexts) {}

    public function preview(Department $department, int $year, int $month, ?int $generatedBy = null): ScheduleRun
    {
        return DB::transaction(function () use ($department, $year, $month, $generatedBy) {
            $this->deletePreview($department, $year, $month);

            $context = $this->contexts->make($department, $year, $month);
            $built = $this->build($context);

            $run = ScheduleRun::query()->create([
                'department_id' => $department->id,
                'type' => $this->type(),
                'year' => $year,
                'month' => $month,
                'status' => RunStatus::Preview,
                'fairness' => $built['fairness'],
                'generated_by' => $generatedBy,
            ]);

            $now = now();
            $rows = array_map(fn (array $row) => $row + [
                'schedule_run_id' => $run->id,
                'department_id' => $department->id,
                'is_published' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ], $built['rows']);

            foreach (array_chunk($rows, 500) as $chunk) {
                ScheduleAssignment::query()->insert($chunk);
            }

            return $run->load('assignments');
        });
    }

    abstract protected function type(): AssignmentType;

    /**
     * @return array{rows: list<array<string, mixed>>, fairness: array<string, mixed>}
     */
    abstract protected function build(RosterContext $context): array;

    protected function deletePreview(Department $department, int $year, int $month): void
    {
        ScheduleRun::query()
            ->where('department_id', $department->id)
            ->where('type', $this->type())
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', RunStatus::Preview)
            ->delete();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<array<string, mixed>>  $gaps
     * @param  list<array<string, mixed>>  $skipped
     * @param  list<string>  $notes
     * @return array<string, mixed>
     */
    protected function fairness(RosterContext $context, array $rows, array $gaps, array $skipped = [], array $notes = []): array
    {
        $byDoctor = [];
        foreach ($context->doctors as $doctor) {
            $byDoctor[$doctor->id] = [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'duties' => 0,
                'shifts' => ['Morning' => 0, 'Evening' => 0, 'Night' => 0],
                'holiday_duties' => 0,
            ];
        }

        foreach ($rows as $row) {
            $id = $row['user_id'];
            if (! isset($byDoctor[$id])) {
                continue;
            }
            $byDoctor[$id]['duties']++;
            if (! empty($row['shift'])) {
                $byDoctor[$id]['shifts'][$row['shift']]++;
            }
            if (isset($context->holidays[$row['date']])) {
                $byDoctor[$id]['holiday_duties']++;
            }
        }

        $duties = array_column($byDoctor, 'duties');
        $spread = $duties === [] ? 0 : max($duties) - min($duties);

        return [
            'doctors' => array_values($byDoctor),
            'coverage_gaps' => $gaps,
            'skipped_requests' => $skipped,
            'spread' => $spread,
            'holiday_dates' => $context->holidays,
            'notes' => $notes,
        ];
    }
}

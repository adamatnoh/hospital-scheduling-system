<?php

namespace App\Exports;

use App\Models\ScheduleAssignment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RosterExport implements FromCollection, WithHeadings
{
    /**
     * @param  Collection<int, ScheduleAssignment>  $assignments
     */
    public function __construct(private Collection $assignments) {}

    public function collection(): Collection
    {
        return $this->assignments->map(fn (ScheduleAssignment $assignment) => [
            $assignment->date->toDateString(),
            $assignment->user->name,
            $assignment->type->label(),
            $assignment->shift?->value,
            $assignment->ward?->name,
            $assignment->department->name,
        ]);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['Date', 'Doctor', 'Type', 'Shift', 'Ward', 'Department'];
    }
}

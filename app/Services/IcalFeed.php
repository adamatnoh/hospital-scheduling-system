<?php

namespace App\Services;

use App\Models\ScheduleAssignment;
use App\Models\User;

class IcalFeed
{
    public function forUser(User $user): string
    {
        $assignments = ScheduleAssignment::query()
            ->with('ward')
            ->where('user_id', $user->id)
            ->where('is_published', true)
            ->orderBy('date')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//HSS//Hospital Scheduling System//EN',
            'CALSCALE:GREGORIAN',
            'X-WR-CALNAME:HSS '.$user->displayName(),
        ];

        foreach ($assignments as $assignment) {
            $stamp = $assignment->updated_at?->utc()->format('Ymd\THis\Z') ?? now()->utc()->format('Ymd\THis\Z');
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:hss-assignment-'.$assignment->id.'@hss';
            $lines[] = 'DTSTAMP:'.$stamp;
            $lines[] = 'DTSTART;VALUE=DATE:'.$assignment->date->format('Ymd');
            $lines[] = 'SUMMARY:'.$this->escape($assignment->label());
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    private function escape(string $value): string
    {
        return str_replace(["\\", ";", ",", "\n"], ["\\\\", "\\;", "\\,", " "], $value);
    }
}

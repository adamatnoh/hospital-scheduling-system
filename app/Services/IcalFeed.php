<?php

namespace App\Services;

use App\Enums\AssignmentType;
use App\Models\ScheduleAssignment;
use App\Models\User;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;

/**
 * Builds an RFC 5545 calendar of one doctor's published duties.
 */
class IcalFeed
{
    /** How far back the feed reaches, so a long-serving doctor's feed stays small. */
    private const MONTHS_BACK = 6;

    public function forUser(User $user): string
    {
        $tz = (string) config('app.timezone');

        $assignments = ScheduleAssignment::query()
            ->with(['ward', 'department'])
            ->where('user_id', $user->id)
            ->where('is_published', true)
            ->whereDate('date', '>=', Carbon::now($tz)->subMonths(self::MONTHS_BACK)->startOfMonth()->toDateString())
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//HSS//Hospital Scheduling System//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->text('HSS – '.$user->displayName()),
            'X-WR-CALDESC:'.$this->text('Published duties from '.config('hss.hospital')),
            'X-WR-TIMEZONE:'.$tz,
            'REFRESH-INTERVAL;VALUE=DURATION:PT1H',
            'X-PUBLISHED-TTL:PT1H',
            ...$this->timezone($tz),
        ];

        foreach ($assignments as $assignment) {
            array_push($lines, ...$this->event($assignment, $tz));
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map($this->fold(...), $lines))."\r\n";
    }

    /**
     * @return list<string>
     */
    private function event(ScheduleAssignment $assignment, string $tz): array
    {
        $stamp = ($assignment->updated_at ?? $assignment->created_at ?? Carbon::create(2026))->utc()->format('Ymd\THis\Z');
        $day = $assignment->date->format('Y-m-d');
        $department = $assignment->department->name ?? 'HSS';
        $hours = $assignment->type === AssignmentType::Shift && $assignment->shift
            ? config('hss.shift_hours.'.$assignment->shift->value)
            : null;

        $lines = [
            'BEGIN:VEVENT',
            'UID:hss-assignment-'.$assignment->id.'@hss',
            'DTSTAMP:'.$stamp,
            'LAST-MODIFIED:'.$stamp,
        ];

        if ($hours) {
            $start = Carbon::parse($day.' '.$hours[0], $tz);
            $end = Carbon::parse($day.' '.$hours[1], $tz);
            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }
            $lines[] = 'DTSTART;TZID='.$tz.':'.$start->format('Ymd\THis');
            $lines[] = 'DTEND;TZID='.$tz.':'.$end->format('Ymd\THis');
        } else {
            $date = Carbon::parse($day, $tz);
            $lines[] = 'DTSTART;VALUE=DATE:'.$date->format('Ymd');
            $lines[] = 'DTEND;VALUE=DATE:'.$date->copy()->addDay()->format('Ymd');
        }

        [$summary, $detail] = match ($assignment->type) {
            AssignmentType::OnCall => ['On call · '.$department, 'On call for '.$department.'.'],
            AssignmentType::Ward => ['Ward · '.($assignment->ward->name ?? 'Ward'), ($assignment->ward->name ?? 'Ward').' ward, '.$department.'.'],
            AssignmentType::Shift => [($assignment->shift->value ?? 'Shift').' shift', ($assignment->shift->value ?? 'Shift').' shift'.($hours ? ' ('.$hours[0].'–'.$hours[1].')' : '').', '.$department.'.'],
        };

        $location = config('hss.hospital').($assignment->type === AssignmentType::Ward && $assignment->ward ? ', '.$assignment->ward->name : '');

        array_push($lines,
            'SUMMARY:'.$this->text($summary),
            'DESCRIPTION:'.$this->text($detail."\nPublished roster from HSS."),
            'LOCATION:'.$this->text($location),
            'CATEGORIES:'.$this->text($assignment->type->label()),
            'STATUS:CONFIRMED',
            'TRANSP:OPAQUE',
            'END:VEVENT',
        );

        return $lines;
    }

    /**
     * A VTIMEZONE for the app timezone. Malaysia has no daylight saving, so a
     * single fixed-offset STANDARD rule is exact for Asia/Kuala_Lumpur.
     *
     * @return list<string>
     */
    private function timezone(string $tz): array
    {
        $now = new DateTimeImmutable('now', new DateTimeZone($tz));
        $seconds = $now->getOffset();
        $offset = sprintf('%s%02d%02d', $seconds < 0 ? '-' : '+', intdiv(abs($seconds), 3600), intdiv(abs($seconds) % 3600, 60));

        return [
            'BEGIN:VTIMEZONE',
            'TZID:'.$tz,
            'X-LIC-LOCATION:'.$tz,
            'BEGIN:STANDARD',
            'DTSTART:19700101T000000',
            'TZOFFSETFROM:'.$offset,
            'TZOFFSETTO:'.$offset,
            'TZNAME:'.$now->format('T'),
            'END:STANDARD',
            'END:VTIMEZONE',
        ];
    }

    private function text(string $value): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\\;', '\\,', '\\n', '\\n'], $value);
    }

    /**
     * Fold lines longer than 75 octets without splitting a UTF-8 character.
     */
    private function fold(string $line): string
    {
        $parts = [];
        $limit = 75;
        while (strlen($line) > $limit) {
            $chunk = mb_strcut($line, 0, $limit, 'UTF-8');
            $parts[] = $chunk;
            $line = substr($line, strlen($chunk));
            $limit = 74;
        }
        $parts[] = $line;

        return implode("\r\n ", $parts);
    }
}

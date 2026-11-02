<?php

namespace App\Enums;

enum AssignmentType: string
{
    case OnCall = 'on_call';
    case Ward = 'ward';
    case Shift = 'shift';

    public function label(): string
    {
        return match ($this) {
            self::OnCall => 'On Call',
            self::Ward => 'Ward',
            self::Shift => 'Shift',
        };
    }
}

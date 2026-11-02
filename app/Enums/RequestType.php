<?php

namespace App\Enums;

enum RequestType: string
{
    case Leave = 'leave';
    case OnCall = 'on_call';
    case Swap = 'swap';

    public function label(): string
    {
        return match ($this) {
            self::Leave => 'Leave',
            self::OnCall => 'On Call',
            self::Swap => 'Shift Swap',
        };
    }
}

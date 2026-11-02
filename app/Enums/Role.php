<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Scheduler = 'scheduler';
    case Regular = 'regular';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Scheduler => 'Scheduler',
            self::Regular => 'Doctor',
        };
    }

    public function isDoctor(): bool
    {
        return $this !== self::Admin;
    }
}

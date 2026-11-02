<?php

namespace App\Enums;

enum Shift: string
{
    case Morning = 'Morning';
    case Evening = 'Evening';
    case Night = 'Night';

    public function colour(): string
    {
        return match ($this) {
            self::Morning => '#8da399',
            self::Evening => '#d65302',
            self::Night => '#311F62',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

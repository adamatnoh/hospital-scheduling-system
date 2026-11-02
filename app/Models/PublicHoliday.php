<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $date
 * @property string $name
 * @property string $state
 */
class PublicHoliday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'state',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}

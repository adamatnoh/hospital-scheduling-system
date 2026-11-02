<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

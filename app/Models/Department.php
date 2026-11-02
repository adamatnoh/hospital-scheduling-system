<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'doctors_per_on_call_day',
        'monthly_on_call_cap',
        'rest_after_night',
        'include_weekends',
        'ward_block_days',
        'holiday_state',
    ];

    protected function casts(): array
    {
        return [
            'doctors_per_on_call_day' => 'integer',
            'monthly_on_call_cap' => 'integer',
            'rest_after_night' => 'boolean',
            'include_weekends' => 'boolean',
            'ward_block_days' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ScheduleRun::class);
    }

    public function doctors(): HasMany
    {
        return $this->users()->where('role', '!=', 'admin')->orderBy('name');
    }
}

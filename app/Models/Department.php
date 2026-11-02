<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property int $doctors_per_on_call_day
 * @property int $monthly_on_call_cap
 * @property bool $rest_after_night
 * @property bool $include_weekends
 * @property int $ward_block_days
 * @property string $holiday_state
 */
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

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<Ward, $this> */
    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class);
    }

    /** @return HasMany<ScheduleRun, $this> */
    public function runs(): HasMany
    {
        return $this->hasMany(ScheduleRun::class);
    }

    /** @return HasMany<User, $this> */
    public function doctors(): HasMany
    {
        return $this->users()->where('role', '!=', 'admin')->orderBy('name');
    }
}

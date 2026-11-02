<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'role',
        'email',
        'department_id',
        'password',
        'calendar_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'calendar_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ScheduleRequest::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isScheduler(): bool
    {
        return $this->role === Role::Scheduler;
    }

    public function displayName(): string
    {
        return $this->role?->isDoctor() ? 'Dr '.$this->name : $this->name;
    }

    public function sharesDepartment(?int $departmentId): bool
    {
        return $this->isAdmin() || ($departmentId !== null && $this->department_id === $departmentId);
    }
}

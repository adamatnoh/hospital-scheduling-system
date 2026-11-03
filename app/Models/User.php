<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property Role $role
 * @property int|null $department_id
 * @property string|null $calendar_token Legacy plain-text feed token, cleared on reset.
 * @property string|null $calendar_feed_token Encrypted feed token.
 * @property string|null $calendar_feed_hash SHA-256 of the feed token, used for lookups.
 */
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'role',
        'email',
        'department_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'calendar_token',
        'calendar_feed_token',
        'calendar_feed_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'calendar_feed_token' => 'encrypted',
        ];
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return HasMany<ScheduleRequest, $this> */
    public function requests(): HasMany
    {
        return $this->hasMany(ScheduleRequest::class);
    }

    /** @return HasMany<ScheduleAssignment, $this> */
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

    /**
     * The secret token in this user's calendar feed URL, created on first use.
     */
    public function calendarFeedToken(): string
    {
        try {
            $token = $this->calendar_feed_token;
        } catch (DecryptException) {
            $token = null; // APP_KEY changed; hand out a fresh link.
        }

        return $token ?: $this->resetCalendarFeedToken();
    }

    /**
     * Issue a new feed token. Any link handed out before stops working.
     */
    public function resetCalendarFeedToken(): string
    {
        $token = Str::random(40);

        $this->forceFill([
            'calendar_feed_token' => $token,
            'calendar_feed_hash' => self::hashCalendarToken($token),
            'calendar_token' => null,
        ])->save();

        return $token;
    }

    public static function hashCalendarToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function findByCalendarFeedToken(string $token): ?self
    {
        return static::query()->where('calendar_feed_hash', self::hashCalendarToken($token))->first();
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

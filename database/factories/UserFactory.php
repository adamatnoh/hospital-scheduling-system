<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'role' => Role::Regular,
            'email' => $this->faker->unique()->safeEmail(),
            'department_id' => Department::factory(),
            'email_verified_at' => now(),
            'password' => 'password',
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => Role::Admin,
            'department_id' => null,
        ]);
    }

    public function scheduler(): static
    {
        return $this->state(fn () => [
            'role' => Role::Scheduler,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}

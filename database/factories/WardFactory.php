<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ward>
 */
class WardFactory extends Factory
{
    protected $model = Ward::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'colour' => $this->faker->hexColor(),
        ];
    }
}

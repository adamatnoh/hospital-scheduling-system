<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Orthopedics', 'Emergency']).' '.$this->faker->unique()->numerify('###'),
            'doctors_per_on_call_day' => 2,
            'monthly_on_call_cap' => 6,
            'rest_after_night' => false,
            'include_weekends' => false,
            'ward_block_days' => 14,
            'holiday_state' => 'Johor',
        ];
    }
}

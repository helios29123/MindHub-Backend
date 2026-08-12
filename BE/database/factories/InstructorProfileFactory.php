<?php

namespace Database\Factories;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstructorProfileFactory extends Factory
{
    protected $model = InstructorProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => fake()->paragraph(),
            'expertise' => fake()->words(3, true),
            'experience_years' => fake()->numberBetween(1, 20),
            'level' => 'Senior',
        ];
    }
}

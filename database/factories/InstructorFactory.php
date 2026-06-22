<?php

namespace Database\Factories;


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstructorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'avatar' => 'https://i.pravatar.cc/150?u=' . fake()->uuid(),
            'bio' => fake()->sentence(12),
        ];
    }
}
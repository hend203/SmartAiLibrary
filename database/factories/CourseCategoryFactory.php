<?php

namespace Database\Factories;



use Illuminate\Database\Eloquent\Factories\Factory;

class CourseCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Programming', 'Design', 'Business', 'Marketing',
                'Data Science', 'Mobile Development', 'Personal Development',
                'Photography', 'Music', 'Language Learning',
            ]),
            'icon' => null,
            'is_active' => true,
        ];
    }
}

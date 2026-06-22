<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraphs(3, true),
            'thumbnail' => 'https://placehold.co/400x300/222222/FFFFFF/png?text=Course+Cover',
            // 'category_id' => \App\Models\CourseCategory::factory(),
            // 'instructor_id' => \App\Models\Instructor::factory(),
            'rating' => fake()->randomFloat(1, 1, 5),
            'students_count' => fake()->numberBetween(50, 8000),
            'is_free' => fake()->boolean(),
            'is_featured' => fake()->boolean(20),
        ];
    }
}
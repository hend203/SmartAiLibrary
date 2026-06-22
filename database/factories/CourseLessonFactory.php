<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;

class CourseLessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => \App\Models\Course::factory(),
            'title' => 'Lesson: ' . fake()->sentence(4),
            'video_path' => 'courses/videos/dummy.mp4', // ← مسار وهمي، زي file_path بتاعة الكتب
            'pdf_path' => 'courses/pdfs/dummy.pdf',
            'duration_seconds' => fake()->numberBetween(120, 900),
            'order' => fake()->numberBetween(0, 5),
        ];
    }
}

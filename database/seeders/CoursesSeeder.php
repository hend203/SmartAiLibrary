<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLesson;
use App\Models\Instructor;
use Illuminate\Database\Seeder;

class CoursesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء التصنيفات بدون تكرار
        $categoryNames = [
            'Programming', 'Design', 'Business', 'Marketing',
            'Data Science', 'Mobile Development', 'Personal Development',
            'Photography', 'Music', 'Language Learning'
        ];

        $categories = collect($categoryNames)->map(function ($name) {
            return CourseCategory::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        });

        // 2. إنشاء Instructors
        $instructors = Instructor::factory()->count(25)->create();

        // 3. إنشاء الكورسات
        Course::factory()
            ->count(120)
            ->create()
            ->each(function ($course) use ($categories, $instructors) {

                $course->update([
                    'category_id'   => $categories->random()->id,        // random بدل cycle
                    'instructor_id' => $instructors->random()->id,
                    'is_free'       => rand(0, 1) === 1,
                    'is_featured'   => rand(1, 100) <= 15, // 15% featured
                ]);

                // إضافة دروس
                CourseLesson::factory()
                    ->count(rand(4, 12))
                    ->create(['course_id' => $course->id]);
            });

        $this->command->info('Courses Seeder Completed Successfully!');
    }
}
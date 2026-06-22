<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CourseResource;
use App\Http\Resources\Api\InstructorResource;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorProfileController extends Controller
{
    public function show(Request $request, $id)
    {
        $instructor = Instructor::findOrFail($id);

        $instructorData = [
            'id' => $instructor->id,
            'name' => $instructor->name,
            'image' => $instructor->avatar,
            'bio' => $instructor->bio,
            'students_count' => Course::where('instructor_id', $id)->sum('students_count'),
        ];

        $popularCourses = Course::where('instructor_id', $id)
            ->orderBy('students_count', 'desc')
            ->limit(5)
            ->get();

        $latestCourses = Course::where('instructor_id', $id)
            ->latest()
            ->limit(5)
            ->get();

        $categoryIds = Course::where('instructor_id', $id)->pluck('category_id')->unique();
        $similarInstructors = Instructor::where('id', '!=', $id)
            ->whereHas('courses', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            })
            ->limit(6)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'instructor' => $instructorData,
                'popular_courses' => CourseResource::collection($popularCourses),
                'latest_courses'  => CourseResource::collection($latestCourses),
                'similar_instructors' => InstructorResource::collection($similarInstructors),
            ]
        ]);
    }
}
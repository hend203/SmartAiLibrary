<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseHomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $dailyRecommended = Course::with(['instructor', 'category'])
            ->where('is_featured', true)
            ->inRandomOrder()
            ->limit(2)
            ->get();

        $continueWatching = Course::query()
            ->join('course_progress', 'courses.id', '=', 'course_progress.course_id')
            ->where('course_progress.user_id', $user->id)
            ->where('course_progress.completed', false)
            ->orderByDesc('course_progress.updated_at')
            ->select('courses.*')
            ->with('instructor')
            ->limit(5)
            ->get();

        $newReleases = Course::with('instructor')->latest()->limit(10)->get();

        $watchedCategoryIds = Course::query()
            ->join('course_progress', 'courses.id', '=', 'course_progress.course_id')
            ->where('course_progress.user_id', $user->id)
            ->pluck('courses.category_id')
            ->unique();

        $youMightLike = Course::with('instructor')
            ->whereIn('category_id', $watchedCategoryIds)
            ->orWhere('rating', '>', 4)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'daily_recommended' => CourseResource::collection($dailyRecommended),
                'continue_watching'  => CourseResource::collection($continueWatching),
                'new_releases'       => CourseResource::collection($newReleases),
                'you_might_like'     => CourseResource::collection($youMightLike),
            ]
        ]);
    }
}
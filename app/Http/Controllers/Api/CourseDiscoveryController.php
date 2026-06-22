<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CourseCategoryResource;
use App\Http\Resources\Api\CourseResource;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;

class CourseDiscoveryController extends Controller
{
    public function index()
    {
        // ✅ إزالة التكرارات + ترتيب
        $categories = CourseCategory::where('is_active', true)
                        ->select('id', 'name', 'icon')
                        ->distinct()                    // مهم جداً
                        ->orderBy('name')
                        ->get();

        $newReleases = Course::with('instructor')
                        ->latest()
                        ->limit(8)
                        ->get();

        $topTrending = Course::with('instructor')
                        ->orderBy('students_count', 'desc')
                        ->limit(8)
                        ->get();

        $featured = Course::with('instructor')
                        ->where('is_featured', true)
                        ->limit(6)
                        ->get();

        $topFree = Course::with('instructor')
                        ->where('is_free', true)
                        ->orderBy('students_count', 'desc')
                        ->limit(8)
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'categories'    => CourseCategoryResource::collection($categories),
                'new_releases'  => CourseResource::collection($newReleases),
                'top_trending'  => CourseResource::collection($topTrending),
                'featured'      => CourseResource::collection($featured),
                'top_free'      => CourseResource::collection($topFree),
                // Charts (لو عايز تضيف Charts Section)
                'charts'        => [
                    [
                        'id'    => 'trending',
                        'title' => 'Top Trending',
                        'image' => 'https://placehold.co/600x300/FF6B6B/FFFFFF/png?text=Trending'
                    ],
                    [
                        'id'    => 'free',
                        'title' => 'Top Free',
                        'image' => 'https://placehold.co/600x300/4ECDC4/FFFFFF/png?text=Free+Courses'
                    ],
                    [
                        'id'    => 'new_release',
                        'title' => 'New Releases',
                        'image' => 'https://placehold.co/600x300/45B7D1/FFFFFF/png?text=New'
                    ],
                ]
            ]
        ]);
    }

    // باقي الدوال (مش محتاجة تغيير كبير)
    public function search(Request $request)
    {
        $query = $request->get('q');

        $courses = Course::with('instructor')
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhereHas('instructor', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => CourseResource::collection($courses)
        ]);
    }

    public function topTrendingList()
    {
        $courses = Course::with('instructor')
            ->orderBy('students_count', 'desc')
            ->paginate(20);

        return CourseResource::collection($courses)->additional([
            'meta' => ['title' => 'Top Trending Courses', 'subtitle' => 'mix by Nubook']
        ]);
    }

    public function topFreeList()
    {
        $courses = Course::with('instructor')
            ->where('is_free', true)
            ->orderBy('students_count', 'desc')
            ->paginate(20);

        return CourseResource::collection($courses)->additional([
            'meta' => ['title' => 'Top Free Courses', 'subtitle' => 'mix by Nubook']
        ]);
    }

    public function coursesByCategory($categoryId)
    {
        $courses = Course::with('instructor')
            ->where('category_id', $categoryId)
            ->paginate(20);

        $category = CourseCategory::find($categoryId);

        return CourseResource::collection($courses)->additional([
            'meta' => [
                'title' => $category->name ?? 'Category',
                'subtitle' => 'mix by Nubook'
            ]
        ]);
    }
}
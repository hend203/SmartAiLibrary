<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookResource;
use App\Http\Resources\Api\CourseResource;
use App\Http\Resources\Api\InstructorResource;
use App\Models\Author;
use App\Models\Book;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\SearchHistory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // البحث الأولي
    public function getInitialData(Request $request)
    {
        $user = $request->user();

        $recent = SearchHistory::where('user_id', $user->id)
            ->latest()
            ->distinct('query')
            ->limit(5)
            ->get(['id', 'query']);

        $popular = [
            ['id' => 1, 'name' => 'Fiksi & Sastra'],
            ['id' => 2, 'name' => 'Cerita Anak'],
            ['id' => 3, 'name' => 'Technology'],
            ['id' => 4, 'name' => 'Programming'],
            ['id' => 5, 'name' => 'Design'],
        ];

        return response()->json([
            'recent_searches' => $recent,
            'popular_searches' => $popular
        ]);
    }

    // البحث العام — بيشمل كتب + مؤلفين + كورسات + instructors دفعة واحدة
    public function globalSearch(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json(['data' => []]);

        SearchHistory::updateOrCreate(
            ['user_id' => $request->user()->id, 'query' => $query],
            ['created_at' => now()]
        );

        $books = Book::with('author')
            ->where('title', 'LIKE', "%{$query}%")
            ->limit(3)->get();

        $people = Author::where('name', 'LIKE', "%{$query}%")
            ->limit(3)->get();

        // ← جديد
        $courses = Course::with('instructor')
            ->where('title', 'LIKE', "%{$query}%")
            ->limit(3)->get();

        // ← جديد
        $instructors = Instructor::where('name', 'LIKE', "%{$query}%")
            ->limit(3)->get();

        return response()->json([
            'query' => $query,
            'results' => [
                'books' => BookResource::collection($books),
                'authors_and_narrators' => $people,
                'courses' => CourseResource::collection($courses),         // ← جديد
                'instructors' => InstructorResource::collection($instructors), // ← جديد
            ]
        ]);
    }

    // البحث المتخصص — بيشمل books + narrators + courses + instructors
    public function searchByType(Request $request, $type)
    {
        $query = $request->get('q');

        if ($type === 'books') {
            $results = Book::with('author')->where('title', 'LIKE', "%{$query}%")->get();
            return BookResource::collection($results);
        }

        if ($type === 'narrators') {
            $results = Author::where('is_narrator', true)
                ->where('name', 'LIKE', "%{$query}%")->get();
            return response()->json(['data' => $results]);
        }

        // ← جديد
        if ($type === 'courses') {
            $results = Course::with('instructor')
                ->where('title', 'LIKE', "%{$query}%")->get();
            return CourseResource::collection($results);
        }

        // ← جديد
        if ($type === 'instructors') {
            $results = Instructor::where('name', 'LIKE', "%{$query}%")->get();
            return InstructorResource::collection($results);
        }

        return response()->json(['message' => 'Invalid type'], 400);
    }

    // حذف سجل البحث
    public function clearHistory(Request $request, $id = null)
    {
        if ($id) {
            SearchHistory::where('user_id', $request->user()->id)->where('id', $id)->delete();
        } else {
            SearchHistory::where('user_id', $request->user()->id)->delete();
        }

        return response()->json(['message' => 'History cleared']);
    }
}
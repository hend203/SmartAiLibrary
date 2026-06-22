<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookResource;
use App\Http\Resources\Api\AuthorResource;
use App\Http\Resources\Api\CourseResource;
use App\Http\Resources\Api\InstructorResource;
use App\Models\Book;
use App\Models\Author;
use App\Models\Course;
use App\Models\CourseFavorite;
use App\Models\Instructor;
use App\Models\InstructorFavorite;
use App\Models\PersonalShelf;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // جلب المفضلة حسب النوع
    public function index(Request $request, $type)
    {
        $user = $request->user();

        // ───── كتب ─────
        if ($type === 'book') {
            $bookIds = $user->favoriteBooks()->pluck('book_id');
            $books = Book::with('author')->whereIn('id', $bookIds)->paginate(15);
            return BookResource::collection($books);
        }

        // ───── مؤلفين ─────
        if ($type === 'author') {
            $authors = $user->favoriteAuthors()
                ->where('is_narrator', false)
                ->paginate(15);
            return AuthorResource::collection($authors);
        }

        // ───── رواة ─────
        if ($type === 'narator') {
            $narrators = $user->favoriteAuthors()
                ->where('is_narrator', true)
                ->paginate(15);
            return AuthorResource::collection($narrators);
        }

        // ───── كورسات (جديد) ─────
        if ($type === 'course') {
            $courseIds = CourseFavorite::where('user_id', $user->id)->pluck('course_id');
            $courses = Course::with(['instructor', 'category'])
                ->whereIn('id', $courseIds)
                ->paginate(15);
            return CourseResource::collection($courses);
        }

        // ───── instructors (جديد) ─────
        if ($type === 'instructor') {
            $instructorIds = InstructorFavorite::where('user_id', $user->id)->pluck('instructor_id');
            $instructors = Instructor::whereIn('id', $instructorIds)->paginate(15);
            return InstructorResource::collection($instructors);
        }

        return response()->json(['message' => 'Invalid type'], 400);
    }

    // Toggle كتاب
    public function toggleBookFavorite(Request $request, $id)
    {
        $shelf = PersonalShelf::updateOrCreate(
            ['user_id' => $request->user()->id, 'book_id' => $id],
        );

        $shelf->is_favorite = !$shelf->is_favorite;
        $shelf->save();

        return response()->json([
            'status' => $shelf->is_favorite,
            'message' => $shelf->is_favorite ? 'Added to favorites' : 'Removed from favorites'
        ]);
    }

    // Toggle مؤلف/راوي
    public function toggleAuthorFavorite(Request $request, $id)
    {
        $user = $request->user();
        $status = $user->favoriteAuthors()->toggle($id);
        $isAttached = count($status['attached']) > 0;

        return response()->json([
            'status' => $isAttached,
            'message' => $isAttached ? 'Following author' : 'Unfollowed author'
        ]);
    }

    // ← جديد: Toggle كورس
    public function toggleCourseFavorite(Request $request, $id)
    {
        $user = $request->user();

        $existing = CourseFavorite::where('user_id', $user->id)
            ->where('course_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => false, 'message' => 'Removed from favorites']);
        }

        CourseFavorite::create(['user_id' => $user->id, 'course_id' => $id]);

        return response()->json(['status' => true, 'message' => 'Added to favorites']);
    }

    // ← جديد: Toggle instructor
    public function toggleInstructorFavorite(Request $request, $id)
    {
        $user = $request->user();

        $existing = InstructorFavorite::where('user_id', $user->id)
            ->where('instructor_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => false, 'message' => 'Unfollowed instructor']);
        }

        InstructorFavorite::create(['user_id' => $user->id, 'instructor_id' => $id]);

        return response()->json(['status' => true, 'message' => 'Following instructor']);
    }
}
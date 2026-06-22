<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CourseResource;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    // قائمة كل الكورسات
    public function index()
    {
        $courses = Course::with(['instructor', 'category'])
        ->orderBy('id')
        ->get(); // ← من غير paginate
    return CourseResource::collection($courses);
    }

    // تفاصيل كورس واحد + دروسه
    public function show($id)
    {
        $course = Course::with(['lessons', 'category', 'instructor'])->findOrFail($id);
        return response()->json(['data' => new CourseResource($course)]);
    }

    // ← جديد: رفع فيديو + PDF لدرس داخل كورس (زي رفع PDF بتاع الكتاب بالظبط)
    public function uploadLesson(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'video' => 'required|file|mimes:mp4,mov,avi,mkv|max:512000', // 500MB max
            'pdf' => 'nullable|file|mimes:pdf|max:20480', // 20MB max
            'order' => 'nullable|integer',
            'duration_seconds' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ───── رفع الفيديو (نفس طريقة رفع PDF الكتاب) ─────
        $videoFile = $request->file('video');
        $videoFileName = time() . '_' . $videoFile->getClientOriginalName();
        $videoPath = $videoFile->storeAs('courses/videos', $videoFileName, 'public');

        // ───── رفع الـ PDF لو موجود ─────
        $pdfPath = null;
        if ($request->hasFile('pdf')) {
            $pdfFile = $request->file('pdf');
            $pdfFileName = time() . '_' . $pdfFile->getClientOriginalName();
            $pdfPath = $pdfFile->storeAs('courses/pdfs', $pdfFileName, 'public');
        }

        $lesson = CourseLesson::create([
            'course_id' => $course->id,
            'title' => $request->title,
            'video_path' => $videoPath,
            'pdf_path' => $pdfPath,
            'duration_seconds' => $request->duration_seconds ?? 0,
            'order' => $request->order ?? 0,
        ]);

        return response()->json([
            'message' => 'Lesson uploaded successfully',
            'data' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'video_url' => asset('storage/' . $lesson->video_path),
                'pdf_url' => $lesson->pdf_path ? asset('storage/' . $lesson->pdf_path) : null,
                'duration_seconds' => $lesson->duration_seconds,
                'order' => $lesson->order,
            ],
        ]);
    }

    // ← جديد: حذف درس (بيمسح الملفات الفعلية من الـ storage كمان)
  public function deleteLesson($lessonId)
{
    $lesson = CourseLesson::findOrFail($lessonId);

    if ($lesson->video_path) {
        Storage::disk('public')->delete($lesson->video_path); // ← مش \Storage
    }

    if ($lesson->pdf_path) {
        Storage::disk('public')->delete($lesson->pdf_path); // ← مش \Storage
    }

    $lesson->delete();

    return response()->json(['message' => 'Lesson deleted successfully']);
}

    // ← جديد: إنشاء كورس جديد (الأدمن)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|file|image|max:5120', // 5MB max
            'category_id' => 'nullable|exists:course_categories,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'is_free' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbFile = $request->file('thumbnail');
            $thumbFileName = time() . '_' . $thumbFile->getClientOriginalName();
            $thumbnailPath = $thumbFile->storeAs('courses/thumbnails', $thumbFileName, 'public');
        }

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'thumbnail' => $thumbnailPath ? asset('storage/' . $thumbnailPath) : null,
            'category_id' => $request->category_id,
            'instructor_id' => $request->instructor_id,
            'is_free' => $request->is_free ?? false,
            'is_featured' => $request->is_featured ?? false,
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'data' => new CourseResource($course),
        ]);
    }

    // تسجيل مكان التوقف في الفيديو
    public function updateProgress(Request $request, $courseId)
    {
        $user = $request->user();

        $progress = CourseProgress::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $courseId],
            [
                'lesson_id' => $request->lesson_id,
                'last_position_seconds' => $request->position ?? 0,
                'completed' => $request->completed ?? false,
            ]
        );

        return response()->json(['data' => $progress]);
    }

    // الكورسات اللي اليوزر بدأ يتفرج عليها
    public function continueWatching(Request $request)
    {
        $user = $request->user();

        $courses = Course::query()
            ->join('course_progress', 'courses.id', '=', 'course_progress.course_id')
            ->where('course_progress.user_id', $user->id)
            ->where('course_progress.completed', false)
            ->orderByDesc('course_progress.updated_at')
            ->select('courses.*')
            ->with('instructor')
            ->limit(10)
            ->get();

        return response()->json(['data' => CourseResource::collection($courses)]);
    }
}
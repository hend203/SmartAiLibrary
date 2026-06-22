<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\AuthorProfileController;
use App\Http\Controllers\Api\BookActionController;
use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NewPasswordController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseHomeController;
use App\Http\Controllers\Api\CourseDiscoveryController;
use App\Http\Controllers\Api\InstructorProfileController;
use App\Http\Controllers\Api\InstructorController;


// Authentication Routes (Public)
Route::post('/register', [AuthController::class, 'register']); // إنشاء حساب
Route::post('/login', [AuthController::class, 'login']);       // تسجيل دخول
// Password Reset OTP Flow
Route::post('/forgot-password/send-otp', [NewPasswordController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [NewPasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [NewPasswordController::class, 'resetPassword']);
// Social Auth
Route::get('/auth/{provider}/redirect', [AuthController::class, 'socialRedirect']);
Route::get('/auth/{provider}/callback', [AuthController::class, 'socialCallback']);

 
Route::middleware('auth:sanctum')->group(function () {
   
    Route::get('/discover', [DiscoveryController::class, 'index']);
    Route::get('/search', [DiscoveryController::class, 'search']);

    // Search Routes
    Route::get('/search/initial', [SearchController::class, 'getInitialData']); // الصفحة الفاضية
    Route::get('/search/global', [SearchController::class, 'globalSearch']);    // البحث العام
    Route::get('/search/filter/{type}', [SearchController::class, 'searchByType']); // بحث متخصص
    Route::delete('/search/history/{id?}', [SearchController::class, 'clearHistory']); // مسح السجل

    // Auth Management
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // Onboarding Process (Screenshots)
    Route::post('/profile/update', [OnboardingController::class, 'updateProfile']);
    Route::get('/topics', [OnboardingController::class, 'getTopics']);
    Route::post('/topics/save', [OnboardingController::class, 'saveTopics']);

    // قوائم الـ 20 المخصصة
    Route::get('/discover/trending-20', [DiscoveryController::class, 'topTrendingList']);
    Route::get('/discover/free-20', [DiscoveryController::class, 'topFreeList']);
    Route::get('/discover/category/{categoryId}', [DiscoveryController::class, 'booksByCategory']);

    // روابط المؤلفين والفنانين
    Route::get('/authors', [AuthorController::class, 'index']);
    Route::get('/authors/{id}/similar', [AuthorController::class, 'similar']);

    // الحصول على القوائم (التبويبات في الصور)
    Route::get('/favorites/{type}', [FavoriteController::class, 'index']); // type: book, author, narator
    // عمليات الـ Toggle (عند الضغط على القلب أو متابعة)
    Route::post('/favorites/book/{id}', [FavoriteController::class, 'toggleBookFavorite']);
    Route::post('/favorites/author/{id}', [FavoriteController::class, 'toggleAuthorFavorite']);
    Route::post('/favorites/course/{id}', [FavoriteController::class, 'toggleCourseFavorite']);
    Route::post('/favorites/instructor/{id}', [FavoriteController::class, 'toggleInstructorFavorite']);
    

    // روابط قائمة الخيارات (Modal Actions)
    Route::post('/books/{id}/favorite', [BookActionController::class, 'toggleFavorite']);
    Route::post('/books/{id}/block', [BookActionController::class, 'blockBook']);
    Route::post('/books/{id}/listen', [BookActionController::class, 'logListen']);
    Route::get('/books/{id}/share', [BookActionController::class, 'shareBook']);

    Route::get('/authors/{id}/profile', [AuthorProfileController::class, 'show']);

    Route::get('/home', [HomeController::class, 'index']);
    Route::delete('/books/{id}/history', [BookActionController::class, 'removeFromHistory']);
    Route::get('/books', [BookController::class, 'index']);
    // ───── Courses ─────
    Route::get('/courses/home', [CourseHomeController::class, 'index']);
    Route::get('/courses/discover', [CourseDiscoveryController::class, 'index']);
    Route::get('/courses/search', [CourseDiscoveryController::class, 'search']);
    Route::get('/courses/trending-20', [CourseDiscoveryController::class, 'topTrendingList']);
    Route::get('/courses/free-20', [CourseDiscoveryController::class, 'topFreeList']);
    Route::get('/courses/category/{categoryId}', [CourseDiscoveryController::class, 'coursesByCategory']);
    Route::get('/courses/continue-watching', [CourseController::class, 'continueWatching']);
    
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::post('/courses/{id}/progress', [CourseController::class, 'updateProgress']);

    // ───── Instructors ─────
    Route::get('/instructors/{id}/profile', [InstructorProfileController::class, 'show']);
    Route::get('/instructors', [InstructorController::class, 'index']);
    Route::get('/instructors/{id}/similar', [InstructorController::class, 'similar']);

    
    // Chatbot
    Route::post('/chatbot/message', function (Request $request) {
        $message = $request->input('message');
        $history = $request->input('history', []);
        $conversationId = $request->input('conversation_id');

        // إنشاء أو تحديث الـ conversation
        if ($conversationId) {
            $conversation = \App\Models\ChatConversation::where('user_id', $request->user()->id)
                ->findOrFail($conversationId);
        } else {
            $conversation = \App\Models\ChatConversation::create([
                'user_id' => $request->user()->id,
                'title'   => mb_substr($message, 0, 50),
            ]);
        }

        // حفظ رسالة المستخدم
        $conversation->messages()->create([
            'role'    => 'user',
            'content' => $message,
        ]);

        $messages = [
            ['role' => 'system', 'content' => 'You are Learnova AI assistant. You help users with audiobooks and e-learning. Be helpful and friendly. Answer in the same language the user writes in.'],
        ];
        foreach ($history as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => 'https://learnova.app',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model'    => 'openai/gpt-oss-20b:free',
            'messages' => $messages,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'AI service unavailable', 'details' => $response->json()], 503);
        }

        $botReply = $response->json('choices.0.message.content') ?? 'عذراً، حدث خطأ.';

        // حفظ رد البوت
        $conversation->messages()->create([
            'role'    => 'assistant',
            'content' => $botReply,
        ]);

        return response()->json([
            'id'              => uniqid(),
            'message'         => $botReply,
            'role'            => 'assistant',
            'conversation_id' => $conversation->id,
        ]);
    });

    // Chat History
    Route::get('/chat/conversations', function (Request $request) {
        $conversations = \App\Models\ChatConversation::where('user_id', $request->user()->id)
            ->withCount('messages')
            ->latest()
            ->get();
        return response()->json($conversations);
    });

    Route::get('/chat/conversations/{id}', function (Request $request, $id) {
        $conversation = \App\Models\ChatConversation::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with('messages')
            ->firstOrFail();
        return response()->json($conversation);
    });

    Route::delete('/chat/conversations/{id}', function (Request $request, $id) {
        \App\Models\ChatConversation::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();
        return response()->json(['message' => 'Deleted successfully']);
    });

    Route::get('/authors/{id}/profile', [AuthorProfileController::class, 'show']);

    Route::get('/home', [HomeController::class, 'index']);
    Route::delete('/books/{id}/history', [BookActionController::class, 'removeFromHistory']);
 Route::get('/books', [BookController::class, 'index']);

});

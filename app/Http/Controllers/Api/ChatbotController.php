<?php

// ============================================================
// Laravel Chatbot Controller
// routes/api.php:  Route::post('/chatbot/message', [ChatbotController::class, 'message']);
// ============================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function message(Request $request)
    {
        $request->validate([
            'message'   => 'required|string|max:1000',
            'history'   => 'nullable|array',
        ]);

        // ============================================================
        // الخيار 1: OpenAI / Gemini مباشرة
        // ============================================================
        $messages = [];

        // System prompt — خصصيه لـ Learnova
        $messages[] = [
            'role'    => 'system',
            'content' => 'You are Learnova AI assistant. You help users with audiobooks, e-learning content, and platform features. Be helpful, concise, and friendly.',
        ];

        // History
        foreach (($request->history ?? []) as $msg) {
            $messages[] = [
                'role'    => $msg['role'],   // 'user' or 'assistant'
                'content' => $msg['content'],
            ];
        }

        // رسالة المستخدم الجديدة
        $messages[] = [
            'role'    => 'user',
            'content' => $request->message,
        ];

        // OpenAI API call
        $response = Http::withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => 'gpt-3.5-turbo',
                'messages'   => $messages,
                'max_tokens' => 500,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'AI service unavailable'], 503);
        }

        $botReply = $response->json('choices.0.message.content');

        return response()->json([
            'id'      => uniqid(),
            'message' => $botReply,
            'role'    => 'assistant',
        ]);

        // ============================================================
        // الخيار 2: LangChain (لو بتستخدمي Python microservice)
        // بدلي الـ Http call فوق بـ:
        // $response = Http::post('http://your-langchain-service/chat', [...]);
        // ============================================================
    }
}

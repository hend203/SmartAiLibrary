<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CourseLesson;

class TranscribeAllLessons extends Command
{
    protected $signature = 'lesson:transcribe:all';

    public function handle()
    {
        // جيب كل الدروس اللي عندها فيديو وملهاش transcript لسه
        $lessons = CourseLesson::whereNotNull('video_path')
                               ->whereNull('transcript')
                               ->get();

        $this->info("عدد الدروس: " . $lessons->count());

        foreach ($lessons as $lesson) {
            $this->info("جاري معالجة الدرس: {$lesson->id}");
            $this->call('lesson:transcribe', ['lesson_id' => $lesson->id]);
        }

        $this->info('✅ تم الانتهاء من كل الدروس!');
    }
}
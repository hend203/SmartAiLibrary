<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CourseLesson;

class TranscribeLesson extends Command
{
    protected $signature = 'lesson:transcribe {lesson_id}';

    public function handle()
    {
        $lesson = CourseLesson::findOrFail($this->argument('lesson_id'));
        $videoPath = storage_path('app/public/' . $lesson->video_path);

        $outputDir = storage_path('app/transcripts');
        if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);

        $pythonPath = 'C:\\Users\\Hind\\AppData\\Local\\Programs\\Python\\Python311\\python.exe';

        $command = "\"{$pythonPath}\" -m whisper \"{$videoPath}\" --model small --output_format txt --output_dir \"{$outputDir}\" 2>&1";
        
        exec($command, $output, $exitCode);

        $filename = pathinfo($videoPath, PATHINFO_FILENAME);
        $transcriptPath = $outputDir . '\\' . $filename . '.txt';

        if (file_exists($transcriptPath)) {
            $transcript = file_get_contents($transcriptPath);
            $lesson->update(['transcript' => $transcript]);
            $this->info('✅ تم حفظ الـ transcript للدرس: ' . $lesson->id);
        } else {
            $this->error('❌ مش لاقي الملف: ' . $transcriptPath);
            $this->line(implode("\n", $output));
        }
    }
}
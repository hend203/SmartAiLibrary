<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
           'category_name' => $this->category?->name,
'category_id' => $this->category?->id,
            'instructor' => $this->whenLoaded('instructor', fn () => [
                'id' => $this->instructor?->id,
                'name' => $this->instructor?->name,
                'avatar' => $this->instructor?->avatar,
            ]),
            'rating' => (float) $this->rating,
            'students_count' => $this->students_count,
            'is_free' => (bool) $this->is_free,
            'is_featured' => (bool) $this->is_featured,
            'lessons_count' => $this->whenLoaded('lessons', fn () => $this->lessons->count()),
            'lessons' => $this->whenLoaded('lessons', fn () => $this->lessons->map(fn ($lesson) => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'video_url' => $lesson->video_path
                    ? asset('storage/' . $lesson->video_path)
                    : null,
                'pdf_url' => $lesson->pdf_path
                    ? asset('storage/' . $lesson->pdf_path)
                    : null,
                'duration_seconds' => $lesson->duration_seconds,
                'order' => $lesson->order,
            ])),
        ];
    }
}
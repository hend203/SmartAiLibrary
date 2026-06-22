<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\Api\InstructorResource;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    // قائمة كل المدرسين
    public function index()
    {
        $instructors = Instructor::paginate(20);
        return InstructorResource::collection($instructors);
    }

    // مدرسين مشابهين (نفس فكرة authors/{id}/similar)
    public function similar(Request $request, $id)
    {
        $categoryIds = Course::where('instructor_id', $id)->pluck('category_id')->unique();

        $similarInstructors = Instructor::where('id', '!=', $id)
            ->whereHas('courses', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            })
            ->limit(10)
            ->get();

        return InstructorResource::collection($similarInstructors);
    }
}
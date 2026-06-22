// database/migrations/..._create_courses_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->foreignId('category_id')->nullable()
                  ->constrained('course_categories')->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()
                  ->constrained('instructors')->nullOnDelete();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('students_count')->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
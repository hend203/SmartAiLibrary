// database/migrations/..._create_instructor_favorites_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'instructor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_favorites');
    }
};
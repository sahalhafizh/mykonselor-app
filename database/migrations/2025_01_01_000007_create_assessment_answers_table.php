<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('symptom_id')->constrained('symptoms')->cascadeOnDelete();
            $table->unsignedTinyInteger('answer_value');
            $table->decimal('cf_user', 4, 3);
            $table->unsignedTinyInteger('dass_score');
            $table->timestamps();

            $table->unique(['assessment_id', 'symptom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answers');
    }
};

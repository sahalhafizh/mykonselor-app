<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->decimal('stress_cf', 5, 3)->default(0);
            $table->decimal('anxiety_cf', 5, 3)->default(0);
            $table->decimal('depression_cf', 5, 3)->default(0);
            $table->unsignedSmallInteger('stress_score')->default(0);
            $table->unsignedSmallInteger('anxiety_score')->default(0);
            $table->unsignedSmallInteger('depression_score')->default(0);
            $table->enum('stress_severity', ['normal', 'ringan', 'sedang', 'berat']);
            $table->enum('anxiety_severity', ['normal', 'ringan', 'sedang', 'berat']);
            $table->enum('depression_severity', ['normal', 'ringan', 'sedang', 'berat']);
            $table->enum('highest_severity', ['normal', 'ringan', 'sedang', 'berat']);
            $table->string('calculation_version', 20)->default('1.0');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_results');
    }
};

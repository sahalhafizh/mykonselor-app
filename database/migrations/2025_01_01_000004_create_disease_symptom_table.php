<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_symptom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disease_id')->constrained('diseases')->cascadeOnDelete();
            $table->foreignId('symptom_id')->constrained('symptoms')->cascadeOnDelete();
            $table->string('rule_code', 10)->nullable();
            $table->decimal('mb', 4, 3);
            $table->decimal('md', 4, 3);
            $table->decimal('cf_pakar', 5, 3);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['disease_id', 'symptom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_symptom');
    }
};

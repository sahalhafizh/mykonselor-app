<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disease_symptom_id')->constrained('disease_symptom')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('old_mb', 4, 3)->nullable();
            $table->decimal('old_md', 4, 3)->nullable();
            $table->decimal('new_mb', 4, 3);
            $table->decimal('new_md', 4, 3);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_change_logs');
    }
};

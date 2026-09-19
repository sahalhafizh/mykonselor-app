<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('study_code', 64);
            $table->unsignedTinyInteger('semester');
            $table->string('program_studi', 100);
            $table->string('protocol_fingerprint', 64);
            $table->string('consent_version', 64);
            $table->json('consent_snapshot');
            $table->timestamp('consented_at');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'study_code']);
        });
        Schema::table('assessments', function (Blueprint $table) {
            // Existing data stays unclassified; never invent historical consent/cohort.
            $table->string('data_context', 16)->default('legacy');
            $table->string('study_code', 64)->nullable();
            $table->foreignId('research_participation_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('research_semester')->nullable();
            $table->json('research_snapshot')->nullable();
            $table->index(['data_context', 'study_code', 'user_id', 'completed_at'], 'assessments_research_lookup');
        });
        Schema::table('export_audit_events', function (Blueprint $table) {
            $table->json('report_context')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('export_audit_events', fn (Blueprint $table) => $table->dropColumn('report_context'));
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropIndex('assessments_research_lookup');
            $table->dropConstrainedForeignId('research_participation_id');
            $table->dropColumn(['data_context', 'study_code', 'research_semester', 'research_snapshot']);
        });
        Schema::dropIfExists('research_participations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('data_consent_at')->nullable()->after('status');
        });

        Schema::table('referral_requests', function (Blueprint $table) {
            $table->foreignId('processed_by')->nullable()->after('catatan')->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable()->after('processed_by');
            $table->unique(['user_id', 'assessment_id'], 'referrals_user_assessment_unique');
        });

        Schema::table('assessment_results', function (Blueprint $table) {
            $table->unique('assessment_id');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_results', function (Blueprint $table) {
            $table->dropUnique(['assessment_id']);
        });

        Schema::table('referral_requests', function (Blueprint $table) {
            $table->dropUnique('referrals_user_assessment_unique');
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['processed_by', 'processed_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('data_consent_at');
        });
    }
};

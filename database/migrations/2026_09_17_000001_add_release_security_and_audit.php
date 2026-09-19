<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim', 12)->nullable()->change();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('temporary_password_expires_at')->nullable();
            $table->timestamp('identity_verified_at')->nullable();
            $table->foreignId('identity_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('consent_version', 40)->nullable();
            $table->json('consent_snapshot')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->unsignedBigInteger('two_factor_last_step')->nullable();
        });
        Schema::create('rule_set_versions', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 64)->unique();
            $table->json('snapshot');
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::table('assessment_results', function (Blueprint $table) {
            $table->foreignId('rule_set_version_id')->nullable()->constrained('rule_set_versions')->restrictOnDelete();
        });
        Schema::create('rule_audit_events', function (Blueprint $table) {
            $table->id();
            // Stable identifiers/snapshots deliberately survive deletion of a rule.
            $table->unsignedBigInteger('disease_id')->index();
            $table->unsignedBigInteger('symptom_id');
            $table->string('symptom_code', 20);
            $table->string('action', 16);
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
        DB::table('rule_change_logs')->orderBy('id')->chunkById(200, function ($logs) {
            foreach ($logs as $log) {
                $rule = DB::table('disease_symptom')->find($log->disease_symptom_id);
                if (! $rule) {
                    continue;
                }
                DB::table('rule_audit_events')->insert([
                    'disease_id' => $rule->disease_id, 'symptom_id' => $rule->symptom_id,
                    'symptom_code' => DB::table('symptoms')->where('id', $rule->symptom_id)->value('kode'),
                    'action' => 'updated', 'actor_id' => $log->changed_by, 'created_at' => $log->created_at,
                    'before_values' => json_encode(['mb' => $log->old_mb, 'md' => $log->old_md]),
                    'after_values' => json_encode(['mb' => $log->new_mb, 'md' => $log->new_md]),
                ]);
            }
        });
        Schema::create('export_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('format', 10);
            $table->boolean('anonymized');
            $table->unsignedInteger('row_count');
            $table->timestamp('from_at')->nullable();
            $table->timestamp('to_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_audit_events');
        Schema::dropIfExists('rule_audit_events');
        Schema::table('assessment_results', fn (Blueprint $table) => $table->dropConstrainedForeignId('rule_set_version_id'));
        Schema::dropIfExists('rule_set_versions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('identity_verified_by');
            $table->dropColumn(['must_change_password', 'temporary_password_expires_at', 'identity_verified_at',
                'consent_version', 'consent_snapshot', 'two_factor_secret', 'two_factor_confirmed_at', 'two_factor_last_step']);
        });
        // NIM remains nullable on rollback: new admin accounts have no student NIM.
    }
};

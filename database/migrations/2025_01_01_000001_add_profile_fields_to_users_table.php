<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim', 12)->unique()->after('id');
            $table->text('no_telp')->nullable()->after('nim');
            $table->string('fakultas')->nullable()->after('email');
            $table->string('program_studi')->nullable()->after('fakultas');
            $table->enum('role', ['mahasiswa', 'admin'])->default('mahasiswa')->after('program_studi');
            $table->enum('theme_preference', ['light', 'dark'])->default('light')->after('role');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('theme_preference');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'no_telp', 'fakultas', 'program_studi', 'role', 'theme_preference', 'status']);
            $table->dropSoftDeletes();
        });
    }
};

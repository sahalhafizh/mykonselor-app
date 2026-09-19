<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\ResearchStudy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || ResearchStudy::isResearch()) {
            return;
        }

        User::firstOrCreate(
            ['nim' => '000000000000'],
            [
                'name' => 'Admin MyKonselor', 'no_telp' => '628110000000',
                'email' => 'admin@mykonselor.test', 'password' => Hash::make('AdminDemoLokal2026!'),
                'role' => 'admin', 'status' => 'aktif', 'email_verified_at' => now(), 'data_consent_at' => now(),
            ]
        );

        $student = User::firstOrCreate(
            ['nim' => '123456789012'],
            [
                'name' => 'Mahasiswa Contoh', 'no_telp' => '628123456789',
                'email' => 'mahasiswa@mykonselor.test', 'password' => Hash::make('password'),
                'fakultas' => 'Fakultas Ilmu Komputer', 'program_studi' => 'Teknik Informatika',
                'role' => 'mahasiswa', 'status' => 'aktif', 'email_verified_at' => now(), 'data_consent_at' => now(),
            ]
        );
        if ($student->wasRecentlyCreated) {
            $student->forceFill(['identity_verified_at' => now()])->save();
        }
    }
}

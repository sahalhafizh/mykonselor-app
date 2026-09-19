<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\User;
use App\Support\BuildAssets;
use App\Support\PrivacyPolicy;
use App\Support\ResearchStudy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class HostingCheck extends Command
{
    protected $signature = 'app:hosting-check';

    protected $description = 'Memeriksa prasyarat rilis; bukan jaminan keamanan server.';

    public function handle(): int
    {
        $host = parse_url(config('app.url', ''), PHP_URL_HOST) ?: '';
        $checks = [
            'APP_ENV harus production' => app()->isProduction(),
            'Mode hosting harus research' => config('research.mode') === 'research' && ResearchStudy::isResearch(),
            'Kode, periode, aturan pemilihan hasil, dan protokol penelitian harus ditetapkan' => ResearchStudy::isReady(),
            'APP_DEBUG harus false' => ! config('app.debug'),
            'APP_URL harus HTTPS dengan domain hosting sebenarnya' => str_starts_with(config('app.url', ''), 'https://')
                && $host !== '' && ! in_array($host, ['localhost', '127.0.0.1', 'example.com']) && ! str_ends_with($host, '.test'),
            'APP_KEY harus valid' => $this->keyIsValid(),
            'Koneksi utama harus MySQL' => config('database.default') === 'mysql',
            'Database memakai akun khusus dengan password, bukan root' => filled(config('database.connections.mysql.username'))
                && config('database.connections.mysql.username') !== 'root' && filled(config('database.connections.mysql.password')),
            'SESSION_DRIVER harus database' => config('session.driver') === 'database',
            'SESSION_SECURE_COOKIE harus true' => config('session.secure') === true,
            'SESSION_ENCRYPT harus true' => config('session.encrypt') === true,
            'Cookie sesi harus HttpOnly' => config('session.http_only') === true,
            'SameSite cookie harus lax atau strict' => in_array(config('session.same_site'), ['lax', 'strict'], true),
            'MFA admin harus diwajibkan' => config('security.require_admin_mfa') === true,
            'Verifikasi identitas mahasiswa harus diwajibkan' => config('security.require_student_verification') === true,
            'Pengelola, kontak, retensi, dan persetujuan kebijakan privasi harus diisi' => PrivacyPolicy::isReady(),
            'TRUSTED_PROXIES tidak boleh mempercayai semua alamat' => ! array_intersect(['*', '**', '0.0.0.0/0', '::/0'], config('security.trusted_proxies', [])),
            'Livewire harus menggunakan build CSP' => config('livewire.csp_safe') === true,
            'public/hot harus tidak ada' => ! file_exists(public_path('hot')),
            'Manifest build dan seluruh file asetnya harus lengkap' => BuildAssets::isComplete(public_path('build')),
        ];
        try {
            DB::connection()->getPdo();
            $ran = app('migration.repository')->getRan();
            $files = array_keys(app('migrator')->getMigrationFiles(database_path('migrations')));
            $checks['Seluruh migrasi harus terpasang'] = count(array_diff($files, $ran)) === 0;
        } catch (\Throwable $e) {
            $checks['Database harus terhubung dan sudah dimigrasikan'] = false;
        }
        try {
            $checks['Tabel sesi dan cache harus tersedia'] = Schema::hasTable('sessions') && Schema::hasTable('cache');
            $checks['Akun demo harus dinonaktifkan'] = ! User::whereIn('nim', ['000000000000', '123456789012'])->where('status', 'aktif')->exists();
            $checks['Database hosting harus bebas data skrining demo atau legacy yang belum ditinjau'] = ! Assessment::withTrashed()->whereIn('data_context', ['demo', 'legacy'])->exists();
            $admins = User::where('role', 'admin')->where('status', 'aktif')->get();
            $checks['Harus ada admin aktif dengan email pribadi dan MFA terkonfirmasi'] = $admins->contains(fn ($u) => $this->adminReady($u));
            $checks['Semua admin aktif harus memakai MFA dan kredensial pribadi'] = $admins->every(fn ($u) => $this->adminReady($u));
        } catch (\Throwable $e) {
            $checks['Skema pengguna dan keamanan harus lengkap'] = false;
        }
        foreach ($checks as $label => $passed) {
            $passed ? $this->info('[OK] '.$label) : $this->error('[PERLU DIPERBAIKI] '.$label);
        }
        $this->line('Tetap uji HTTPS nyata, akses /public, backup & restore, izin file, alur pengguna, dan audit dependensi di server tujuan.');

        return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
    }

    private function adminReady(User $user): bool
    {
        return filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false
            && ! preg_match('/(?:example\.(?:com|org|net)|\.(?:test|invalid))$/i', $user->email)
            && $user->two_factor_confirmed_at !== null && filled($user->two_factor_secret)
            && ! Hash::check('password', $user->password) && ! Hash::check('AdminDemoLokal2026!', $user->password);
    }

    private function keyIsValid(): bool
    {
        try {
            app('encrypter');

            return filled(config('app.key')) && config('app.key') !== 'base64:VGVzdE9ubHlLZXlEb05vdFVzZUluUHJvZHVjdGlvbiE=';
        } catch (\Throwable $e) {
            return false;
        }
    }
}

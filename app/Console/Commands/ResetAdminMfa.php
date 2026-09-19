<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SessionRevoker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetAdminMfa extends Command
{
    protected $signature = 'app:reset-admin-mfa {email}';

    protected $description = 'Pemulihan MFA melalui akses server setelah identitas admin diverifikasi.';

    public function handle(SessionRevoker $sessions): int
    {
        $user = User::where('email', strtolower(trim($this->argument('email'))))->where('role', 'admin')->first();
        if (! $user) {
            $this->error('Admin tidak ditemukan.');

            return self::FAILURE;
        }
        if (! $this->confirm('Identitas pemilik sudah diverifikasi? Reset MFA akan mencabut semua sesi.', false)) {
            return self::FAILURE;
        }
        $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null, 'two_factor_last_step' => null])->save();
        $sessions->revoke($user);
        Log::warning('MFA admin direset melalui konsol', ['admin_id' => $user->id]);
        $this->info('MFA direset. Admin wajib mengaktifkan ulang sebelum membuka data produksi.');

        return self::SUCCESS;
    }
}

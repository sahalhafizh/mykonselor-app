<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SessionRevoker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DisableDemoAccounts extends Command
{
    protected $signature = 'app:disable-demo-accounts';

    protected $description = 'Menonaktifkan dua akun contoh tanpa menghapus data skrining.';

    public function handle(SessionRevoker $sessions): int
    {
        if (! User::where('role', 'admin')->where('status', 'aktif')->where(function ($q) {
            $q->whereNull('nim')->orWhereNotIn('nim', ['000000000000', '123456789012']);
        })->whereNotNull('two_factor_confirmed_at')->exists()) {
            $this->error('Buat admin pribadi dan konfirmasikan Authenticator terlebih dahulu.');

            return self::FAILURE;
        }
        if (! $this->confirm('Nonaktifkan akun contoh 000000000000 dan 123456789012?', false)) {
            return self::FAILURE;
        }
        DB::transaction(function () use ($sessions) {
            foreach (User::whereIn('nim', ['000000000000', '123456789012'])->get() as $user) {
                $user->update(['status' => 'nonaktif']);
                $sessions->revoke($user);
            }
        });
        $this->info('Akun contoh dinonaktifkan. Riwayatnya tetap tersimpan.');

        return self::SUCCESS;
    }
}

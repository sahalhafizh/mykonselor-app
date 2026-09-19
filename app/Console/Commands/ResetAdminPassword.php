<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SessionRevoker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ResetAdminPassword extends Command
{
    protected $signature = 'app:reset-admin-password {email}';

    protected $description = 'Pemulihan password admin oleh pengelola server; MFA tetap aktif.';

    public function handle(SessionRevoker $sessions): int
    {
        $user = User::where('email', strtolower(trim($this->argument('email'))))->where('role', 'admin')->where('status', 'aktif')->first();
        if (! $user) {
            $this->error('Admin aktif tidak ditemukan.');

            return self::FAILURE;
        }
        if (! $this->confirm('Identitas pemilik akun sudah diverifikasi melalui prosedur pengelola?', false)) {
            return self::FAILURE;
        }
        $data = ['password' => $this->secret('Password baru'), 'password_confirmation' => $this->secret('Ulangi password baru')];
        $validator = Validator::make($data, ['password' => ['required', 'string', 'max:72', 'confirmed', Password::defaults()]]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }
        $user->forceFill(['password' => $data['password'], 'must_change_password' => true, 'temporary_password_expires_at' => now()->addDay()])->save();
        $sessions->revoke($user);
        Log::warning('Pemulihan password admin oleh pengelola server', ['user_id' => $user->id]);
        $this->info('Password sementara berlaku 24 jam dan wajib diganti. MFA tetap diperlukan.');

        return self::SUCCESS;
    }
}

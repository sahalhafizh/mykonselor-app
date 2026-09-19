<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Membuat admin dengan password pribadi, tanpa akun demo.';

    public function handle(): int
    {
        $data = [
            'name' => $this->ask('Nama admin'),
            'email' => $this->ask('Email admin'),
            'no_telp' => $this->ask('Nomor telepon (format 628...)'),
            'password' => $this->secret('Password (12–72 karakter, huruf besar/kecil dan angka)'),
            'password_confirmation' => $this->secret('Ulangi password'),
        ];
        $data['email'] = strtolower(trim((string) $data['email']));
        if (app()->isProduction() && preg_match('/(?:example\.(?:com|org|net)|\.(?:test|invalid))$/i', $data['email'])) {
            $this->error('Gunakan email pribadi yang sebenarnya untuk admin produksi.');

            return self::FAILURE;
        }
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_telp' => ['required', 'regex:/^628[0-9]{8,11}$/'],
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::defaults()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        unset($data['password_confirmation']);
        $data['email'] = strtolower(trim($data['email']));
        User::create([...$data, 'nim' => null, 'role' => 'admin', 'status' => 'aktif']);
        $this->info('Admin berhasil dibuat. Masuk melalui /admin/login menggunakan email, lalu aktifkan Authenticator. Password tidak dicatat.');

        return self::SUCCESS;
    }
}

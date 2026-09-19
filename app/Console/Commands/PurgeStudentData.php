<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SessionRevoker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgeStudentData extends Command
{
    protected $signature = 'app:purge-student {nim}';

    protected $description = 'Menghapus permanen data mahasiswa setelah permintaan dan identitas diverifikasi. Tidak berjalan otomatis.';

    public function handle(SessionRevoker $sessions): int
    {
        $user = User::withTrashed()->where('nim', $this->argument('nim'))->where('role', 'mahasiswa')->first();
        if (! $user) {
            $this->error('Mahasiswa tidak ditemukan.');

            return self::FAILURE;
        }
        $this->warn('Tindakan menghapus akun, jawaban, hasil dan rujukan. Tidak menghapus salinan backup secara otomatis.');
        if ($this->ask('Setelah memverifikasi permintaan dan backup, ketik HAPUS '.$user->nim) !== 'HAPUS '.$user->nim) {
            return self::FAILURE;
        }
        $id = $user->id;
        DB::transaction(function () use ($user, $sessions) {
            $sessions->revoke($user);
            $user->forceDelete();
        });
        Log::warning('Data mahasiswa dimusnahkan melalui prosedur pengelola', ['user_id' => $id]);
        $this->info('Data aktif dihapus. Catat permintaan dalam daftar penghapusan backup yang aksesnya dibatasi.');

        return self::SUCCESS;
    }
}

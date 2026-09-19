<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_dapat_melihat_seluruh_informasi_profilnya(): void
    {
        $user = User::factory()->create([
            'name' => 'Mahasiswa Contoh',
            'nim' => '123456789012',
            'no_telp' => '6281234567890',
            'email' => 'mahasiswa@example.test',
        ]);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Mahasiswa Contoh')
            ->assertSee('123456789012')
            ->assertSee('6281234567890')
            ->assertSee('mahasiswa@example.test')
            ->assertSee('Fakultas Ilmu Komputer')
            ->assertSee('Teknik Informatika');
    }

    public function test_mahasiswa_hanya_dapat_memperbarui_email_dan_nomor_telepon(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Tetap',
            'nim' => '123456789012',
            'fakultas' => 'Fakultas Ilmu Komputer',
            'program_studi' => 'Teknik Informatika',
            'role' => 'mahasiswa',
            'status' => 'aktif',
        ]);

        $this->actingAs($user)->patch(route('profile.update'), [
            'email' => 'NAMA.BARU@EXAMPLE.TEST',
            'no_telp' => '0812 3456 7890',
            'nim' => '999999999999',
            'fakultas' => 'Fakultas Lain',
            'program_studi' => 'Program Lain',
            'role' => 'admin',
            'status' => 'nonaktif',
        ])->assertRedirect(route('profile.show'));

        $user->refresh();
        $this->assertSame('Nama Tetap', $user->name);
        $this->assertSame('nama.baru@example.test', $user->email);
        $this->assertSame('6281234567890', $user->no_telp);
        $this->assertSame('123456789012', $user->nim);
        $this->assertSame('Fakultas Ilmu Komputer', $user->fakultas);
        $this->assertSame('Teknik Informatika', $user->program_studi);
        $this->assertSame('mahasiswa', $user->role);
        $this->assertSame('aktif', $user->status);
    }

    public function test_password_tidak_berubah_jika_password_lama_salah(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'salah',
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ])->assertSessionHasErrorsIn('passwordUpdate', 'current_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_mahasiswa_dapat_mengubah_password_dengan_password_lama_yang_benar(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ])->assertRedirect(route('profile.show'));

        $this->assertTrue(Hash::check('PasswordBaru123!', $user->refresh()->password));
    }

    public function test_pengguna_tidak_dapat_menghapus_akun_sendiri(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()->assertDontSee('Hapus Akun')->assertDontSee('delete_password');
        $this->actingAs($user)->delete('/profile', ['delete_password' => 'password'])
            ->assertStatus(405);
        $this->assertNotSoftDeleted($user);
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_dapat_memperbarui_profil_mahasiswa_tanpa_mengubah_data_terkunci(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create([
            'nim' => '123456789012',
            'role' => 'mahasiswa',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->get(route('admin.users.edit', $student))
            ->assertOk()
            ->assertSee('123456789012');

        $this->actingAs($admin)->put(route('admin.users.update', $student), [
            'name' => 'Profil Diperbarui',
            'email' => 'profil.baru@example.test',
            'no_telp' => '081298765432',
            'nim' => '999999999999',
            'fakultas' => 'Fakultas Lain',
            'program_studi' => 'Program Lain',
            'role' => 'admin',
            'status' => 'nonaktif',
        ])->assertRedirect(route('admin.users.edit', $student));

        $student->refresh();
        $this->assertSame('Profil Diperbarui', $student->name);
        $this->assertSame('profil.baru@example.test', $student->email);
        $this->assertSame('6281298765432', $student->no_telp);
        $this->assertSame('123456789012', $student->nim);
        $this->assertSame('Fakultas Ilmu Komputer', $student->fakultas);
        $this->assertSame('Teknik Informatika', $student->program_studi);
        $this->assertSame('mahasiswa', $student->role);
        $this->assertSame('aktif', $student->status);
    }

    public function test_admin_tidak_dapat_menggunakan_form_profil_mahasiswa_untuk_mengubah_admin_lain(): void
    {
        $admin = User::factory()->admin()->create();
        $targetAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.users.edit', $targetAdmin))->assertNotFound();
        $this->actingAs($admin)->put(route('admin.users.update', $targetAdmin), [
            'name' => 'Admin Diubah',
            'email' => 'admin.diubah@example.test',
            'no_telp' => '081234567890',
        ])->assertNotFound();
    }
}

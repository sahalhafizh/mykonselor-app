<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_login_has_no_admin_link_but_admin_login_remains_available(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Gunakan NIM')
            ->assertDontSee('Masuk sebagai admin')->assertDontSee(route('admin.login'), false);
        $this->get(route('admin.login'))->assertOk()->assertViewIs('auth.admin-login');
    }

    public function test_demo_registration_displays_semester_and_saves_semester_eight_without_research_consent(): void
    {
        config(['research.mode' => 'demo']);
        $page = $this->get(route('register'))->assertOk()->assertSee('Pilih semester Anda')
            ->assertSee('Semester 7')->assertSee('Semester 8')->assertDontSee('name="research_consent"', false);
        $this->assertSame(1, substr_count($page->getContent(), 'name="semester"'));
        $this->post(route('register'), $this->validPayload(['semester' => '8', 'registration_semester' => 7]))
            ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();
        $user = User::where('nim', '123456789098')->firstOrFail();
        $this->assertSame(8, $user->registration_semester);
        $this->assertDatabaseCount('research_participations', 0);
        $this->assertNull($user->identity_verified_at);
    }

    #[DataProvider('invalidSemesters')]
    public function test_invalid_semester_cannot_register_or_break_the_error_form(mixed $semester): void
    {
        $this->from(route('register'))->post(route('register'), $this->validPayload(['semester' => $semester]))
            ->assertRedirect(route('register'))->assertSessionHasErrors('semester');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->get(route('register'))->assertOk()->assertSee('id="semester-error"', false);
    }

    public static function invalidSemesters(): array
    {
        return ['missing' => [null], 'empty' => [''], 'below target' => [6], 'above target' => [9],
            'decimal' => ['7.5'], 'text' => ['tujuh'], 'array' => [[7]]];
    }

    public function test_selected_semester_is_preserved_after_another_registration_field_fails(): void
    {
        $this->from(route('register'))->post(route('register'), $this->validPayload(['semester' => '8', 'name' => 'Nama123']))
            ->assertSessionHasErrors('name');
        $page = $this->get(route('register'))->assertOk();
        $this->assertMatchesRegularExpression('/<option\s+value="8"\s+selected>Semester 8<\/option>/', $page->getContent());
        $this->assertDatabaseCount('users', 0);
    }

    public function test_nama_dengan_angka_atau_simbol_ditolak(): void
    {
        $this->post(route('register'), $this->validPayload(['name' => 'Abdullah123']))->assertSessionHasErrors('name');
        $this->post(route('register'), $this->validPayload(['name' => 'Abdullah@Sahal']))->assertSessionHasErrors('name');
    }

    public function test_nim_harus_tepat_dua_belas_digit_dan_unik(): void
    {
        $this->post(route('register'), $this->validPayload(['nim' => '123']))->assertSessionHasErrors('nim');

        User::factory()->create(['nim' => '123456789099']);
        $this->post(route('register'), $this->validPayload(['nim' => '123456789099']))->assertSessionHasErrors('nim');
    }

    public function test_consent_wajib_disetujui(): void
    {
        $payload = $this->validPayload();
        unset($payload['consent']);

        $this->post(route('register'), $payload)->assertSessionHasErrors('consent');
    }

    public function test_registrasi_tidak_memerlukan_fakultas_dan_mengabaikan_role_dari_client(): void
    {
        $response = $this->post(route('register'), $this->validPayload([
            'name' => 'Abdullah   Hafizh Sahal',
            'fakultas' => 'Fakultas Lain',
            'role' => 'admin',
        ]));

        $response->assertRedirect(route('dashboard'));
        $user = User::where('nim', '123456789098')->firstOrFail();

        $this->assertSame('Abdullah Hafizh Sahal', $user->name);
        $this->assertSame('Fakultas Ilmu Komputer', $user->fakultas);
        $this->assertSame('Teknik Informatika', $user->program_studi);
        $this->assertSame(7, $user->registration_semester);
        $this->assertSame('mahasiswa', $user->role);
        $this->assertNotNull($user->data_consent_at);
        $this->assertAuthenticatedAs($user);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Abdullah Hafizh Sahal',
            'nim' => '123456789098',
            'no_telp' => '081234567890',
            'semester' => '7',
            'email' => 'mahasiswa.baru@example.test',
            'password' => 'PasswordAman123!',
            'password_confirmation' => 'PasswordAman123!',
            'consent' => '1',
        ], $overrides);
    }
}

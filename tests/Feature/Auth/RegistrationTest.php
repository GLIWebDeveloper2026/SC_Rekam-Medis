<?php

namespace Tests\Feature\Auth;

use App\Models\PatientPortalAccount;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_available(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Daftar sebagai pasien')
            ->assertSee('langsung gunakan portal')
            ->assertDontSee('persetujuan staf');
    }

    public function test_new_user_can_register_with_email_and_password(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Dokter Baru',
            'username' => 'dokter.baru',
            'email' => 'dokter.baru@sehatbersama.test',
            'birth_date' => '1990-01-01',
            'sex' => 'female',
            'phone' => '081234567890',
            'password' => 'Klinik!2026',
            'password_confirmation' => 'Klinik!2026',
        ])->assertRedirect('/dashboard');

        $user = User::query()->where('email', 'dokter.baru@sehatbersama.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('dokter.baru', $user->username);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->email_verified_at);
        Notification::assertNotSentTo($user, VerifyEmail::class);

        $account = $user->patientPortalAccount()->with('patient')->firstOrFail();

        $this->assertSame(PatientPortalAccount::StatusApproved, $account->status);
        $this->assertNotNull($account->patient);
        $this->assertSame('Dokter Baru', $account->patient->full_name);
        $this->assertSame('female', $account->patient->sex);
        $this->assertSame($account->patient->medical_record_number, $account->claimed_medical_record_number);

        $this->get('/dashboard')->assertRedirectToRoute('patient-portal.index');
        $this->get('/patient-portal')->assertOk();
    }

    public function test_registration_requires_a_unique_username(): void
    {
        User::factory()->create(['username' => 'petugas.satu']);

        $this->post('/register', [
            'name' => 'Petugas Dua',
            'username' => 'petugas.satu',
            'email' => 'petugas.dua@sehatbersama.test',
            'birth_date' => '1990-01-01',
            'sex' => 'male',
            'phone' => '081234567891',
            'password' => 'Klinik!2026',
            'password_confirmation' => 'Klinik!2026',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_registration_requires_patient_demographics(): void
    {
        $this->post('/register', [
            'name' => 'Pasien Baru',
            'username' => 'pasien.baru',
            'email' => 'pasien.baru@sehatbersama.test',
            'password' => 'Klinik!2026',
            'password_confirmation' => 'Klinik!2026',
        ])->assertSessionHasErrors(['birth_date', 'sex', 'phone']);

        $this->assertGuest();
        $this->assertNull(User::query()->where('email', 'pasien.baru@sehatbersama.test')->first());
    }
}

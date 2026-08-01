<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_matches_clinic_information_architecture(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Klinik Pratama Sehat Bersama')
            ->assertSee('Daftar sebagai pasien')
            ->assertSee('Satu alur untuk setiap tahap kunjungan')
            ->assertSee('Jadwal praktik')
            ->assertSee('Keputusan medis tetap berada pada tenaga kesehatan')
            ->assertSee('Akses portal pasien')
            ->assertSee('setelah pendaftaran selesai')
            ->assertSee('clinicLanding');
    }
}

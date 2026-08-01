<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicLandingPageTest extends TestCase
{
    public function test_public_homepage_matches_clinic_information_architecture(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Klinik Pratama Sehat Bersama')
            ->assertSee('Buat Janji Temu')
            ->assertSee('Layanan unggulan')
            ->assertSee('Tim medis yang mendengarkan')
            ->assertSee('Jadwal praktik')
            ->assertSee('Cerita dari pasien')
            ->assertSee('clinicLanding');
    }
}

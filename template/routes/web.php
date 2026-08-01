<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Klinik Terintegrasi
|--------------------------------------------------------------------------
*/

// Halaman Pre-Login Landing Page Publik Klinik
Route::get('/', function () {
    return view('pages.landing');
});

// Halaman Login Akun Individual Staf (FR-01)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Halaman Registrasi Pasien Baru (FR-02)
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Portal Petugas Pendaftaran & Antrean (Otorisasi: Pendaftaran)
Route::get('/pendaftaran', function () {
    return view('pendaftaran.index');
});

// Portal Perawat & Triage (Otorisasi: Perawat)
Route::get('/triage', function () {
    return view('triage.index');
});

// Portal Dokter & Rekam Medis Immutable (Otorisasi: Dokter)
Route::get('/dokter', function () {
    return view('dokter.index');
});

// Portal Farmasi & Apoteker (Otorisasi: Apoteker)
Route::get('/farmasi', function () {
    return view('farmasi.index');
});

// Portal Laporan Pemilik Klinik & Audit Trail (Otorisasi: Pemilik)
Route::get('/laporan', function () {
    return view('laporan.index');
});

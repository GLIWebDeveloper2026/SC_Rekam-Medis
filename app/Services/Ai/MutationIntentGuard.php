<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

class MutationIntentGuard
{
    public function allows(string $toolName, string $message): bool
    {
        $normalized = Str::of($message)->lower()->squish()->toString();
        $patterns = match ($toolName) {
            'create_own_appointment', 'create_patient_appointment' => ['buat janji', 'pesan jadwal', 'jadwalkan', 'book appointment'],
            'reschedule_own_appointment', 'reschedule_patient_appointment' => ['ubah jadwal', 'pindah jadwal', 'reschedule'],
            'cancel_own_appointment', 'cancel_patient_appointment' => ['batalkan', 'batal janji', 'cancel appointment'],
            'check_in_own_appointment', 'check_in_patient' => ['check-in', 'check in', 'sudah tiba', 'daftar ulang'],
            'register_patient' => ['daftarkan pasien', 'buat pasien baru', 'registrasi pasien'],
            default => [],
        };

        return collect($patterns)->contains(
            fn (string $pattern): bool => Str::contains($normalized, $pattern),
        );
    }
}

<?php

namespace App\Actions\PatientPortal;

use App\Models\Patient;
use App\Models\PatientPortalAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovePatientPortalAccount
{
    public function execute(
        PatientPortalAccount $account,
        Patient $patient,
        User $reviewer,
        ?string $notes,
    ): PatientPortalAccount {
        return DB::transaction(function () use ($account, $patient, $reviewer, $notes): PatientPortalAccount {
            $lockedAccount = PatientPortalAccount::query()
                ->whereKey($account)
                ->lockForUpdate()
                ->firstOrFail();
            $lockedPatient = Patient::query()->whereKey($patient)->lockForUpdate()->firstOrFail();

            if ($lockedAccount->status !== PatientPortalAccount::StatusPending) {
                throw ValidationException::withMessages(['account' => 'Permintaan ini sudah diproses.']);
            }

            if ($lockedPatient->status !== 'active') {
                throw ValidationException::withMessages(['patient_id' => 'Pasien tidak aktif.']);
            }

            $isAlreadyLinked = PatientPortalAccount::query()
                ->where('patient_id', $lockedPatient->id)
                ->whereKeyNot($lockedAccount->id)
                ->exists();

            if ($isAlreadyLinked) {
                throw ValidationException::withMessages(['patient_id' => 'Pasien sudah terhubung ke akun portal lain.']);
            }

            $lockedAccount->update([
                'patient_id' => $lockedPatient->id,
                'status' => PatientPortalAccount::StatusApproved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $lockedAccount->fresh(['patient', 'user', 'reviewer']);
        }, attempts: 5);
    }
}

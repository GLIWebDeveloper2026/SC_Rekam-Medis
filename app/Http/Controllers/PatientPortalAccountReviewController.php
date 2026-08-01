<?php

namespace App\Http\Controllers;

use App\Actions\PatientPortal\ApprovePatientPortalAccount;
use App\Http\Requests\PatientPortal\ApprovePatientPortalAccountRequest;
use App\Http\Requests\PatientPortal\RejectPatientPortalAccountRequest;
use App\Models\Patient;
use App\Models\PatientPortalAccount;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PatientPortalAccountReviewController extends Controller
{
    public function __construct(
        private readonly ApprovePatientPortalAccount $approveAccount,
        private readonly AuditTrail $auditTrail,
    ) {}

    public function index(): View
    {
        $accounts = PatientPortalAccount::query()
            ->select([
                'id',
                'user_id',
                'status',
                'claimed_birth_date',
                'claimed_phone',
                'claimed_medical_record_number',
                'created_at',
            ])
            ->with('user:id,name,email')
            ->where('status', PatientPortalAccount::StatusPending)
            ->latest()
            ->paginate(25);

        return view('patient-portal-reviews.index', ['accounts' => $accounts]);
    }

    public function approve(
        ApprovePatientPortalAccountRequest $request,
        PatientPortalAccount $patientPortalAccount,
    ): RedirectResponse {
        $patient = Patient::query()->findOrFail($request->validated('patient_id'));
        $account = $this->approveAccount->execute(
            $patientPortalAccount,
            $patient,
            $request->user(),
            $request->validated('review_notes'),
        );
        $this->auditTrail->record(
            'patient_portal.approved',
            'patient_portal_account',
            $account->id,
            'success',
            $request->user(),
            $patient->id,
        );

        return redirect()->route('patient-portal-reviews.index')->with('status', 'Akun pasien berhasil disetujui.');
    }

    public function reject(
        RejectPatientPortalAccountRequest $request,
        PatientPortalAccount $patientPortalAccount,
    ): RedirectResponse {
        $account = DB::transaction(function () use ($patientPortalAccount, $request): PatientPortalAccount {
            $account = PatientPortalAccount::query()
                ->whereKey($patientPortalAccount)
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->status !== PatientPortalAccount::StatusPending) {
                throw ValidationException::withMessages(['account' => 'Permintaan ini sudah diproses.']);
            }

            $account->update([
                'status' => PatientPortalAccount::StatusRejected,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_notes' => $request->validated('review_notes'),
            ]);

            return $account;
        }, attempts: 5);

        $this->auditTrail->record(
            'patient_portal.rejected',
            'patient_portal_account',
            $account->id,
            'success',
            $request->user(),
            reason: $account->review_notes,
        );

        return redirect()->route('patient-portal-reviews.index')->with('status', 'Permintaan akun pasien ditolak.');
    }
}

<?php

use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\AllergyEntryController;
use App\Http\Controllers\ClinicalDraftController;
use App\Http\Controllers\ClinicalEntryController;
use App\Http\Controllers\ClinicalWorkspaceController;
use App\Http\Controllers\ClinicChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispensingController;
use App\Http\Controllers\EncounterController;
use App\Http\Controllers\MedicalRecordCopyController;
use App\Http\Controllers\PatientAppointmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientIdentifierController;
use App\Http\Controllers\PatientMergeController;
use App\Http\Controllers\PatientPortalAccountReviewController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubstitutionController;
use App\Http\Controllers\TriageController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/account/security', AccountSecurityController::class)
        ->middleware('password.confirm')
        ->name('account.security');
    Route::get('/clinical-workspace', ClinicalWorkspaceController::class)
        ->middleware('permission:clinical.view')
        ->name('clinical.workspace');
    Route::get('/patients', [PatientController::class, 'index'])->middleware('permission:patients.view')->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->middleware('permission:patients.manage')->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->middleware('permission:patients.manage')->name('patients.store');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->middleware('permission:patients.view')->name('patients.show');
    Route::post('/patients/{patient}/identifiers', [PatientIdentifierController::class, 'store'])->middleware('permission:patients.manage')->name('patients.identifiers.store');
    Route::post('/patients/{patient}/allergies', [AllergyEntryController::class, 'store'])->middleware('permission:clinical.create')->name('patients.allergies.store');
    Route::post('/patient-merges', [PatientMergeController::class, 'store'])->middleware('permission:patients.manage')->name('patient-merges.store');
    Route::get('/queue', [QueueController::class, 'index'])->middleware('permission:queue.view')->name('queue.index');
    Route::get('/registrations/create', [RegistrationController::class, 'create'])->middleware('permission:queue.manage')->name('registrations.create');
    Route::post('/registrations', [RegistrationController::class, 'store'])->middleware('permission:queue.manage')->name('registrations.store');
    Route::post('/registrations/{registration}/check-in', [RegistrationController::class, 'checkIn'])->middleware('permission:queue.manage')->name('registrations.check-in');
    Route::post('/queue-tickets/{queueTicket}/triage', [TriageController::class, 'store'])->middleware('permission:triage.manage')->name('queue.triage');
    Route::post('/visits/{visit}/encounters', [EncounterController::class, 'store'])->middleware('permission:clinical.create')->name('encounters.store');
    Route::post('/encounters/{encounter}/clinical-drafts', [ClinicalDraftController::class, 'store'])->middleware('permission:clinical.create')->name('clinical-drafts.store');
    Route::post('/clinical-drafts/{clinicalDraft}/finalize', [ClinicalDraftController::class, 'finalize'])->middleware('permission:clinical.create')->name('clinical-drafts.finalize');
    Route::get('/clinical-entries/{clinicalEntry}', [ClinicalEntryController::class, 'show'])->middleware('permission:clinical.view')->name('clinical-entries.show');
    Route::post('/clinical-entries/{clinicalEntry}/addenda', [ClinicalEntryController::class, 'addendum'])->middleware('permission:clinical.create')->name('clinical-entries.addenda.store');
    Route::post('/encounters/{encounter}/prescriptions', [PrescriptionController::class, 'store'])->middleware('permission:prescriptions.create')->name('prescriptions.store');
    Route::post('/prescriptions/{prescription}/corrections', [PrescriptionController::class, 'correct'])->middleware('permission:prescriptions.create')->name('prescriptions.correct');
    Route::get('/pharmacy', [PharmacyController::class, 'index'])->middleware('permission:pharmacy.manage')->name('pharmacy.index');
    Route::post('/prescription-items/{prescriptionItem}/substitutions', [SubstitutionController::class, 'store'])->middleware('permission:pharmacy.manage')->name('substitutions.store');
    Route::post('/substitution-requests/{substitutionRequest}/verbal-approval', [SubstitutionController::class, 'verbalApproval'])->middleware('permission:pharmacy.manage')->name('substitutions.verbal-approval');
    Route::post('/substitution-requests/{substitutionRequest}/ratify', [SubstitutionController::class, 'ratify'])->middleware('permission:prescriptions.create')->name('substitutions.ratify');
    Route::post('/dispensings', [DispensingController::class, 'store'])->middleware('permission:pharmacy.manage')->name('dispensings.store');
    Route::get('/medical-record-copy-requests', [MedicalRecordCopyController::class, 'index'])->middleware('permission:record-copies.manage')->name('record-copies.index');
    Route::post('/medical-record-copy-requests', [MedicalRecordCopyController::class, 'store'])->middleware('permission:record-copies.manage')->name('record-copies.store');
    Route::post('/medical-record-copy-requests/{medicalRecordCopyRequest}/approve', [MedicalRecordCopyController::class, 'approve'])->middleware('permission:record-copies.approve')->name('record-copies.approve');
    Route::post('/medical-record-copy-requests/{medicalRecordCopyRequest}/generate', [MedicalRecordCopyController::class, 'generate'])->middleware('permission:record-copies.manage')->name('record-copies.generate');
    Route::get('/reports', [ReportController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');

    Route::get('/patient-portal-reviews', [PatientPortalAccountReviewController::class, 'index'])
        ->middleware('permission:patients.manage')
        ->name('patient-portal-reviews.index');
    Route::post('/patient-portal-reviews/{patientPortalAccount}/approve', [PatientPortalAccountReviewController::class, 'approve'])
        ->middleware(['permission:patients.manage', 'throttle:20,1'])
        ->name('patient-portal-reviews.approve');
    Route::post('/patient-portal-reviews/{patientPortalAccount}/reject', [PatientPortalAccountReviewController::class, 'reject'])
        ->middleware(['permission:patients.manage', 'throttle:20,1'])
        ->name('patient-portal-reviews.reject');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/patient-portal/account-status', [PatientPortalController::class, 'status'])
        ->name('patient-portal.status');

    Route::middleware('patient.approved')->group(function (): void {
        Route::get('/patient-portal', [PatientPortalController::class, 'index'])
            ->name('patient-portal.index');
        Route::get('/patient-portal/available-slots', [PatientPortalController::class, 'slots'])
            ->name('patient-portal.slots');
        Route::post('/patient-portal/appointments', [PatientAppointmentController::class, 'store'])
            ->middleware('throttle:12,1')
            ->name('patient-portal.appointments.store');
        Route::put('/patient-portal/appointments/{appointment}', [PatientAppointmentController::class, 'update'])
            ->middleware('throttle:12,1')
            ->name('patient-portal.appointments.update');
        Route::delete('/patient-portal/appointments/{appointment}', [PatientAppointmentController::class, 'destroy'])
            ->middleware('throttle:12,1')
            ->name('patient-portal.appointments.destroy');
        Route::post('/patient-portal/appointments/{appointment}/check-in', [PatientAppointmentController::class, 'checkIn'])
            ->middleware('throttle:12,1')
            ->name('patient-portal.appointments.check-in');
    });

    Route::post('/assistant/messages', ClinicChatController::class)
        ->middleware('throttle:clinic-chat')
        ->name('assistant.messages');
});

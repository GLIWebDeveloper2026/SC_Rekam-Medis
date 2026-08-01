<?php

namespace App\Http\Controllers;

use App\Actions\Appointments\BookAppointment;
use App\Actions\Appointments\CancelAppointment;
use App\Actions\Appointments\CheckInAppointment;
use App\Actions\Appointments\RescheduleAppointment;
use App\Http\Requests\Appointments\CancelPatientAppointmentRequest;
use App\Http\Requests\Appointments\CheckInPatientAppointmentRequest;
use App\Http\Requests\Appointments\ReschedulePatientAppointmentRequest;
use App\Http\Requests\Appointments\StorePatientAppointmentRequest;
use App\Models\Appointment;
use App\Models\ProviderSchedule;
use App\Services\AuditTrail;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;

class PatientAppointmentController extends Controller
{
    public function __construct(
        private readonly BookAppointment $bookAppointment,
        private readonly RescheduleAppointment $rescheduleAppointment,
        private readonly CancelAppointment $cancelAppointment,
        private readonly CheckInAppointment $checkInAppointment,
        private readonly AuditTrail $auditTrail,
    ) {}

    public function store(StorePatientAppointmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $schedule = ProviderSchedule::query()->findOrFail($data['provider_schedule_id']);
        $patient = $request->user()->patientPortalAccount->patient()->firstOrFail();
        $appointment = $this->bookAppointment->execute(
            $patient,
            $schedule,
            CarbonImmutable::createFromFormat('Y-m-d', $data['appointment_date'], config('clinic.timezone')),
            $data['slot_start'],
            $data['payer_type'],
            $request->user(),
        );

        $this->auditTrail->record(
            'patient.appointment.booked',
            'appointment',
            $appointment->id,
            'success',
            $request->user(),
            $patient->id,
        );

        return redirect()->route('patient-portal.index')->with('status', 'Janji temu berhasil dibuat.');
    }

    public function update(
        ReschedulePatientAppointmentRequest $request,
        Appointment $appointment,
    ): RedirectResponse {
        $data = $request->validated();
        $schedule = ProviderSchedule::query()->findOrFail($data['provider_schedule_id']);
        $updatedAppointment = $this->rescheduleAppointment->execute(
            $appointment,
            $schedule,
            CarbonImmutable::createFromFormat('Y-m-d', $data['appointment_date'], config('clinic.timezone')),
            $data['slot_start'],
            $request->user(),
        );

        $this->auditTrail->record(
            'patient.appointment.rescheduled',
            'appointment',
            $updatedAppointment->id,
            'success',
            $request->user(),
            $updatedAppointment->registration->patient_id,
        );

        return redirect()->route('patient-portal.index')->with('status', 'Janji temu berhasil dijadwalkan ulang.');
    }

    public function destroy(
        CancelPatientAppointmentRequest $request,
        Appointment $appointment,
    ): RedirectResponse {
        $cancelledAppointment = $this->cancelAppointment->execute(
            $appointment,
            $request->user(),
            $request->validated('cancellation_reason'),
        );

        $this->auditTrail->record(
            'patient.appointment.cancelled',
            'appointment',
            $cancelledAppointment->id,
            'success',
            $request->user(),
            $cancelledAppointment->registration->patient_id,
        );

        return redirect()->route('patient-portal.index')->with('status', 'Janji temu berhasil dibatalkan.');
    }

    public function checkIn(
        CheckInPatientAppointmentRequest $request,
        Appointment $appointment,
    ): RedirectResponse {
        $result = $this->checkInAppointment->execute($appointment, $request->user());

        $this->auditTrail->record(
            'patient.appointment.checked_in',
            'visit',
            $result['visit']->id,
            'success',
            $request->user(),
            $result['visit']->patient_id,
            metadata: ['queue_ticket_id' => $result['queue_ticket']->id],
        );

        return redirect()->route('patient-portal.index')->with('status', 'Check-in berhasil. Nomor antrean Anda sudah tersedia.');
    }
}

<?php

namespace App\Services\Ai\Tools;

use App\Actions\Appointments\BookAppointment;
use App\Actions\Appointments\CancelAppointment;
use App\Actions\Appointments\CheckInAppointment;
use App\Actions\Appointments\RescheduleAppointment;
use App\Data\Ai\ChatActorContext;
use App\Data\Ai\ToolResult;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\ProviderSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;

class AppointmentToolHandler
{
    public function __construct(
        private readonly BookAppointment $bookAppointment,
        private readonly RescheduleAppointment $rescheduleAppointment,
        private readonly CancelAppointment $cancelAppointment,
        private readonly CheckInAppointment $checkInAppointment,
    ) {}

    /** @param array<string, mixed> $arguments */
    public function execute(ChatActorContext $actor, string $toolName, array $arguments): ToolResult
    {
        return match ($toolName) {
            'get_own_appointments' => $this->getOwn($actor),
            'create_own_appointment' => $this->createOwn($actor, $arguments),
            'reschedule_own_appointment' => $this->rescheduleOwn($actor, $arguments),
            'cancel_own_appointment' => $this->cancelOwn($actor, $arguments),
            'check_in_own_appointment' => $this->checkInOwn($actor, $arguments),
            'create_patient_appointment' => $this->createForPatient($actor, $arguments),
            'reschedule_patient_appointment' => $this->rescheduleForPatient($actor, $arguments),
            'cancel_patient_appointment' => $this->cancelForPatient($actor, $arguments),
            'check_in_patient' => $this->checkInPatient($actor, $arguments),
            default => new ToolResult(false, 'unknown_tool', 'Tool janji temu tidak dikenal.'),
        };
    }

    private function getOwn(ChatActorContext $actor): ToolResult
    {
        if (! $actor->isApprovedPatient()) {
            return new ToolResult(false, 'forbidden', 'Tool ini hanya tersedia untuk pasien yang disetujui.');
        }

        $appointments = Appointment::query()
            ->select(['id', 'registration_id', 'provider_schedule_id', 'appointment_date', 'slot_start', 'slot_end', 'status'])
            ->with([
                'registration:id,patient_id,booking_code,requested_service',
                'schedule:id,provider_user_id,service_type',
                'schedule.provider:id,name',
            ])
            ->whereHas('registration', fn ($query) => $query->where('patient_id', $actor->patient->id))
            ->whereDate('appointment_date', '>=', now(config('clinic.timezone'))->subDays(30)->toDateString())
            ->latest('appointment_date')
            ->limit(20)
            ->get()
            ->map(fn (Appointment $appointment): array => $this->safeAppointment($appointment))
            ->all();

        return new ToolResult(true, 'appointments_found', 'Daftar janji temu ditemukan.', ['appointments' => $appointments]);
    }

    /** @param array<string, mixed> $arguments */
    private function createOwn(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->isApprovedPatient()) {
            return new ToolResult(false, 'forbidden', 'Tool ini hanya tersedia untuk pasien yang disetujui.');
        }

        return $this->create($actor, $actor->patient, $arguments);
    }

    /** @param array<string, mixed> $arguments */
    private function createForPatient(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->can('queue.manage')) {
            return new ToolResult(false, 'forbidden', 'Anda tidak memiliki izin membuat janji temu pasien.');
        }

        $patientId = Validator::make($arguments, [
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
        ])->validate()['patient_id'];

        return $this->create($actor, Patient::query()->findOrFail($patientId), $arguments);
    }

    /** @param array<string, mixed> $arguments */
    private function create(ChatActorContext $actor, Patient $patient, array $arguments): ToolResult
    {
        $validated = Validator::make($arguments, [
            'provider_schedule_id' => ['required', 'uuid', 'exists:provider_schedules,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'slot_start' => ['required', 'date_format:H:i,H:i:s'],
            'payer_type' => ['required', 'in:general,insurance,other'],
        ])->validate();
        $appointment = $this->bookAppointment->execute(
            $patient,
            ProviderSchedule::query()->findOrFail($validated['provider_schedule_id']),
            CarbonImmutable::createFromFormat('Y-m-d', $validated['appointment_date'], config('clinic.timezone')),
            $validated['slot_start'],
            $validated['payer_type'],
            $actor->user,
            'ai_assistant',
        );

        return new ToolResult(
            true,
            'appointment_created',
            'Janji temu berhasil dibuat.',
            $this->safeAppointment($appointment),
            'appointment',
            $appointment->id,
        );
    }

    /** @param array<string, mixed> $arguments */
    private function rescheduleOwn(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->isApprovedPatient()) {
            return new ToolResult(false, 'forbidden', 'Tool ini hanya tersedia untuk pasien yang disetujui.');
        }

        $appointment = $this->ownedAppointment($actor, $arguments);

        return $this->reschedule($actor, $appointment, $arguments);
    }

    /** @param array<string, mixed> $arguments */
    private function rescheduleForPatient(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->can('queue.manage')) {
            return new ToolResult(false, 'forbidden', 'Anda tidak memiliki izin mengubah janji temu pasien.');
        }

        return $this->reschedule($actor, $this->appointmentFromArguments($arguments), $arguments);
    }

    /** @param array<string, mixed> $arguments */
    private function reschedule(ChatActorContext $actor, Appointment $appointment, array $arguments): ToolResult
    {
        $validated = Validator::make($arguments, [
            'provider_schedule_id' => ['required', 'uuid', 'exists:provider_schedules,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'slot_start' => ['required', 'date_format:H:i,H:i:s'],
        ])->validate();
        $updated = $this->rescheduleAppointment->execute(
            $appointment,
            ProviderSchedule::query()->findOrFail($validated['provider_schedule_id']),
            CarbonImmutable::createFromFormat('Y-m-d', $validated['appointment_date'], config('clinic.timezone')),
            $validated['slot_start'],
            $actor->user,
        );

        return new ToolResult(true, 'appointment_rescheduled', 'Janji temu berhasil dijadwalkan ulang.', $this->safeAppointment($updated), 'appointment', $updated->id);
    }

    /** @param array<string, mixed> $arguments */
    private function cancelOwn(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->isApprovedPatient()) {
            return new ToolResult(false, 'forbidden', 'Tool ini hanya tersedia untuk pasien yang disetujui.');
        }

        return $this->cancel($actor, $this->ownedAppointment($actor, $arguments), $arguments);
    }

    /** @param array<string, mixed> $arguments */
    private function cancelForPatient(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->can('queue.manage')) {
            return new ToolResult(false, 'forbidden', 'Anda tidak memiliki izin membatalkan janji temu pasien.');
        }

        return $this->cancel($actor, $this->appointmentFromArguments($arguments), $arguments);
    }

    /** @param array<string, mixed> $arguments */
    private function cancel(ChatActorContext $actor, Appointment $appointment, array $arguments): ToolResult
    {
        $reason = Validator::make($arguments, [
            'cancellation_reason' => ['required', 'string', 'max:1000'],
        ])->validate()['cancellation_reason'];
        $cancelled = $this->cancelAppointment->execute($appointment, $actor->user, $reason);

        return new ToolResult(true, 'appointment_cancelled', 'Janji temu berhasil dibatalkan.', $this->safeAppointment($cancelled), 'appointment', $cancelled->id);
    }

    /** @param array<string, mixed> $arguments */
    private function checkInOwn(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->isApprovedPatient()) {
            return new ToolResult(false, 'forbidden', 'Tool ini hanya tersedia untuk pasien yang disetujui.');
        }

        return $this->checkIn($actor, $this->ownedAppointment($actor, $arguments));
    }

    /** @param array<string, mixed> $arguments */
    private function checkInPatient(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->can('queue.manage')) {
            return new ToolResult(false, 'forbidden', 'Anda tidak memiliki izin check-in pasien.');
        }

        return $this->checkIn($actor, $this->appointmentFromArguments($arguments));
    }

    private function checkIn(ChatActorContext $actor, Appointment $appointment): ToolResult
    {
        $result = $this->checkInAppointment->execute($appointment, $actor->user);

        return new ToolResult(true, 'appointment_checked_in', 'Check-in berhasil.', [
            ...$this->safeAppointment($result['appointment']),
            'queue_number' => $result['queue_ticket']->queue_number,
            'queue_status' => $result['queue_ticket']->status,
        ], 'visit', $result['visit']->id);
    }

    /** @param array<string, mixed> $arguments */
    private function ownedAppointment(ChatActorContext $actor, array $arguments): Appointment
    {
        $appointment = $this->appointmentFromArguments($arguments);
        if ($appointment->registration->patient_id !== $actor->patient?->id) {
            throw new AuthorizationException('Janji temu tidak dimiliki oleh pasien ini.');
        }

        return $appointment;
    }

    /** @param array<string, mixed> $arguments */
    private function appointmentFromArguments(array $arguments): Appointment
    {
        $appointmentId = Validator::make($arguments, [
            'appointment_id' => ['required', 'uuid', 'exists:appointments,id'],
        ])->validate()['appointment_id'];

        return Appointment::query()->with('registration')->findOrFail($appointmentId);
    }

    /** @return array<string, mixed> */
    private function safeAppointment(Appointment $appointment): array
    {
        $appointment->loadMissing(['registration', 'schedule.provider']);

        return [
            'appointment_id' => $appointment->id,
            'booking_code' => $appointment->registration->booking_code,
            'service' => $appointment->schedule->service_type,
            'provider' => $appointment->schedule->provider->name,
            'date' => $appointment->appointment_date->toDateString(),
            'slot_start' => substr($appointment->slot_start, 0, 5),
            'slot_end' => substr($appointment->slot_end, 0, 5),
            'status' => $appointment->status,
        ];
    }
}

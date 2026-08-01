<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PatientPortalAccount;
use App\Models\ProviderSchedule;
use App\Models\QueueTicket;
use App\Queries\PatientVisitHistory;
use App\Services\Appointments\AppointmentAvailability;
use App\Services\AuditTrail;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientPortalController extends Controller
{
    public function __construct(
        private readonly PatientVisitHistory $visitHistory,
        private readonly AuditTrail $auditTrail,
    ) {}

    public function status(Request $request): View
    {
        $account = $request->user()
            ->patientPortalAccount()
            ->with('reviewer:id,name')
            ->firstOrFail();

        return view('patient-portal.account-status', ['account' => $account]);
    }

    public function index(Request $request): View
    {
        $account = $request->user()
            ->patientPortalAccount()
            ->with('patient')
            ->firstOrFail();

        abort_unless($account->status === PatientPortalAccount::StatusApproved && $account->patient !== null, 403);

        $patient = $account->patient;
        $appointments = Appointment::query()
            ->select([
                'id',
                'registration_id',
                'provider_schedule_id',
                'appointment_date',
                'slot_start',
                'slot_end',
                'status',
            ])
            ->with([
                'registration:id,patient_id,booking_code,payer_type,requested_service,status',
                'schedule:id,provider_user_id,service_type',
                'schedule.provider:id,name',
            ])
            ->whereHas('registration', fn ($query) => $query->where('patient_id', $patient->id))
            ->whereDate('appointment_date', '>=', now(config('clinic.timezone'))->toDateString())
            ->whereIn('status', [Appointment::StatusBooked, Appointment::StatusCheckedIn])
            ->orderBy('appointment_date')
            ->orderBy('slot_start')
            ->get();
        $currentQueue = QueueTicket::query()
            ->select([
                'id',
                'registration_id',
                'service_date',
                'service_type',
                'queue_number',
                'current_priority',
                'status',
                'checked_in_at',
            ])
            ->with('registration:id,patient_id,booking_code')
            ->whereHas('registration', fn ($query) => $query->where('patient_id', $patient->id))
            ->whereDate('service_date', now(config('clinic.timezone'))->toDateString())
            ->whereIn('status', ['booked', 'waiting', 'triaged', 'called'])
            ->orderByDesc('checked_in_at')
            ->first();
        $schedules = ProviderSchedule::query()
            ->select([
                'id',
                'provider_user_id',
                'service_type',
                'day_of_week',
                'start_time',
                'end_time',
                'slot_duration_minutes',
                'slot_capacity',
                'effective_from',
                'effective_until',
                'status',
            ])
            ->with('provider:id,name')
            ->where('status', 'active')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        $visits = $this->visitHistory->paginate($patient);

        $this->auditTrail->record(
            'patient.visit_history.viewed',
            'patient',
            $patient->id,
            'success',
            $request->user(),
            $patient->id,
        );

        return view('patient-portal.index', compact(
            'account',
            'patient',
            'appointments',
            'currentQueue',
            'schedules',
            'visits',
        ));
    }

    public function slots(Request $request, AppointmentAvailability $availability): JsonResponse
    {
        $validated = $request->validate([
            'provider_schedule_id' => ['required', 'uuid', 'exists:provider_schedules,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $schedule = ProviderSchedule::query()->with('provider:id,name')->findOrFail($validated['provider_schedule_id']);
        $date = CarbonImmutable::createFromFormat('Y-m-d', $validated['appointment_date'], config('clinic.timezone'));
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $dayName = $dayNames[$date->isoWeekday()] ?? 'hari tersebut';

        if (! $schedule->isAvailableOn($date)) {
            return response()->json([
                'is_available' => false,
                'message' => "Dokter {$schedule->provider->name} tidak memiliki jadwal praktik pada hari {$dayName} ({$date->format('d-m-Y')}).",
                'available_count' => 0,
                'slots' => [],
            ]);
        }

        $slots = $availability->availableSlots($schedule, $date);

        return response()->json([
            'is_available' => true,
            'doctor_name' => $schedule->provider->name,
            'service_type' => str($schedule->service_type)->headline()->toString(),
            'day_name' => $dayName,
            'date_formatted' => $date->format('d-m-Y'),
            'available_count' => $slots->count(),
            'slot_duration' => $schedule->slot_duration_minutes,
            'slots' => $slots->values(),
        ]);
    }
}

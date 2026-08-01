<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PatientPortalAccount;
use App\Models\ProviderSchedule;
use App\Models\QueueTicket;
use App\Queries\PatientVisitHistory;
use App\Services\AuditTrail;
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
}

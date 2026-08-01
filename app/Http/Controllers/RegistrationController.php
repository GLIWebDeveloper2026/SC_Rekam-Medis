<?php

namespace App\Http\Controllers;

use App\Actions\Queue\CheckInRegistration;
use App\Actions\Queue\RegisterForService;
use App\Models\Patient;
use App\Models\ProviderSchedule;
use App\Models\Registration;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('queue.create', [
            'patients' => Patient::query()->where('status', 'active')->orderBy('full_name')->limit(100)->get(),
            'schedules' => ProviderSchedule::query()->with('provider')->where('status', 'active')->orderBy('day_of_week')->get(),
        ]);
    }

    public function store(Request $request, RegisterForService $register, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'provider_schedule_id' => ['required', 'uuid', 'exists:provider_schedules,id'],
            'channel' => ['required', 'in:phone,front_desk,other'],
            'payer_type' => ['required', 'in:general,insurance,other'],
            'coverage_id' => ['nullable', 'string', 'max:255'],
            'requested_service' => ['required', 'in:general,dental,nursing'],
        ]);
        $registration = $register->execute($data, $request->user()->id);
        $auditTrail->record('registration.created', 'registration', $registration->id, 'success', $request->user(), $registration->patient_id);

        return redirect()->route('queue.index')->with('status', 'Pendaftaran dan nomor antrean berhasil dibuat.');
    }

    public function checkIn(
        Request $request,
        Registration $registration,
        CheckInRegistration $checkIn,
        AuditTrail $auditTrail,
    ): RedirectResponse {
        $visit = $checkIn->execute($registration, $request->user()->id);
        $auditTrail->record('registration.checked_in', 'visit', $visit->id, 'success', $request->user(), $visit->patient_id);

        return redirect()->route('queue.index')->with('status', 'Pasien berhasil check-in.');
    }
}

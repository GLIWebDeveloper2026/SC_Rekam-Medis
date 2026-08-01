<?php

namespace App\Http\Requests\Appointments;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class CancelPatientAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');
        $patientId = $this->user()?->patientPortalAccount?->patient_id;

        return $appointment instanceof Appointment
            && $patientId !== null
            && $appointment->registration()->where('patient_id', $patientId)->exists();
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['cancellation_reason' => ['required', 'string', 'max:1000']];
    }
}

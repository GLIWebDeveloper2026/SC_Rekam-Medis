<?php

namespace App\Http\Requests\Appointments;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class ReschedulePatientAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->ownsAppointment();
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'provider_schedule_id' => ['required', 'uuid', 'exists:provider_schedules,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:tomorrow'],
            'slot_start' => ['required', 'date_format:H:i'],
        ];
    }

    private function ownsAppointment(): bool
    {
        $appointment = $this->route('appointment');
        $patientId = $this->user()?->patientPortalAccount?->patient_id;

        return $appointment instanceof Appointment
            && $patientId !== null
            && $appointment->registration()->where('patient_id', $patientId)->exists();
    }
}

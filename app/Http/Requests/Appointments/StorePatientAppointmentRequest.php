<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->patientPortalAccount?->isApproved() === true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'provider_schedule_id' => ['required', 'uuid', 'exists:provider_schedules,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'slot_start' => ['required', 'date_format:H:i,H:i:s'],
            'payer_type' => ['required', 'in:general,insurance,other'],
        ];
    }
}

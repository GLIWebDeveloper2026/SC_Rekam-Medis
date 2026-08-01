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
            'slot_start' => ['required', 'date_format:H:i,H:i:s', 'regex:/^(0[0-9]|1[0-9]|2[0-3]):(00|30)(:00)?$/'],
            'payer_type' => ['required', 'in:general,insurance,other'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slot_start.regex' => 'Jam kunjungan harus dalam kelipatan 30 menit (contoh: 10:30, 12:30). Jam 12:20 tidak dapat dipilih.',
        ];
    }
}

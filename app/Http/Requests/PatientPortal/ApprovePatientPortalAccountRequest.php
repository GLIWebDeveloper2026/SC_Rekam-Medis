<?php

namespace App\Http\Requests\PatientPortal;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePatientPortalAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('patients.manage') === true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

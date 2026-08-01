<?php

namespace App\Http\Requests\PatientPortal;

use Illuminate\Foundation\Http\FormRequest;

class RejectPatientPortalAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('patients.manage') === true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['review_notes' => ['required', 'string', 'max:1000']];
    }
}

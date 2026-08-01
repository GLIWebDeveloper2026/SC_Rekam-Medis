<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('record-copies.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_relationship' => ['required', 'string', 'max:50'],
            'purpose' => ['required', 'string', 'min:5', 'max:2000'],
            'requested_period_start' => ['required', 'date'],
            'requested_period_end' => ['required', 'date', 'after_or_equal:requested_period_start'],
            'requested_scope' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}

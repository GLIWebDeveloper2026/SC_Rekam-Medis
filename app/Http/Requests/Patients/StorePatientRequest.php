<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('patients.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:male,female,unknown'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'nik' => ['nullable', 'digits:16'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $birthDate = $this->date('birth_date');
            $isInfantWithoutNik = $birthDate?->greaterThan(now()->subYear()) && ! $this->filled('nik');

            if ($isInfantWithoutNik && (! $this->filled('guardian_name') || ! $this->filled('guardian_relationship'))) {
                $validator->errors()->add('guardian_name', 'Bayi tanpa NIK wajib memiliki data wali dan hubungan wali.');
            }
        }];
    }
}

<?php

namespace App\Services\Ai\Tools;

use App\Actions\Patients\RegisterPatient;
use App\Data\Ai\ChatActorContext;
use App\Data\Ai\ToolResult;
use App\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as LaravelValidator;

class RegistrationToolHandler
{
    public function __construct(private readonly RegisterPatient $registerPatient) {}

    /** @param array<string, mixed> $arguments */
    public function execute(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->can('patients.manage')) {
            return new ToolResult(false, 'forbidden', 'Anda tidak memiliki izin mendaftarkan pasien.');
        }

        $validator = Validator::make($arguments, [
            'full_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'sex' => ['required', 'in:male,female,unknown'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'nik' => ['nullable', 'digits:16'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
        ]);
        $validator->after(function (LaravelValidator $validator) use ($arguments): void {
            $birthDate = filled($arguments['birth_date'] ?? null)
                ? CarbonImmutable::parse($arguments['birth_date'], config('clinic.timezone'))
                : null;
            $isInfantWithoutNik = $birthDate?->greaterThan(now()->subYear())
                && blank($arguments['nik'] ?? null);

            if ($isInfantWithoutNik && (blank($arguments['guardian_name'] ?? null) || blank($arguments['guardian_relationship'] ?? null))) {
                $validator->errors()->add('guardian_name', 'Bayi tanpa NIK wajib memiliki data wali.');
            }
        });
        $validated = $validator->validate();
        $possibleDuplicate = Patient::query()
            ->whereDate('birth_date', $validated['birth_date'])
            ->where('full_name', $validated['full_name'])
            ->when(
                filled($validated['phone'] ?? null),
                fn ($query) => $query->where('phone', $validated['phone']),
            )
            ->exists();
        $patient = $this->registerPatient->execute($validated, $actor->user->id);

        return new ToolResult(true, 'patient_registered', 'Pasien berhasil didaftarkan.', [
            'patient_id' => $patient->id,
            'medical_record_number' => $patient->medical_record_number,
            'full_name' => $patient->full_name,
            'possible_duplicate_warning' => $possibleDuplicate,
        ], 'patient', $patient->id);
    }
}

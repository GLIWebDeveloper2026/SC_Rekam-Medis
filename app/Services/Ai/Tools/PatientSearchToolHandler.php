<?php

namespace App\Services\Ai\Tools;

use App\Data\Ai\ChatActorContext;
use App\Data\Ai\ToolResult;
use App\Models\Patient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PatientSearchToolHandler
{
    /** @param array<string, mixed> $arguments */
    public function execute(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->can('patients.view')) {
            return new ToolResult(false, 'forbidden', 'Anda tidak memiliki izin mencari pasien.');
        }

        $validated = Validator::make($arguments, [
            'query' => ['required', 'string', 'min:2', 'max:100'],
        ])->validate();
        $query = Str::squish($validated['query']);
        $patients = Patient::query()
            ->select(['id', 'medical_record_number', 'full_name', 'birth_date', 'phone', 'status'])
            ->where(function ($builder) use ($query): void {
                $builder->where('full_name', 'like', "%{$query}%")
                    ->orWhere('normalized_name', 'like', '%'.Str::lower($query).'%')
                    ->orWhere('medical_record_number', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderBy('full_name')
            ->limit(10)
            ->get()
            ->map(fn (Patient $patient): array => [
                'id' => $patient->id,
                'medical_record_number' => $patient->medical_record_number,
                'full_name' => $patient->full_name,
                'birth_date' => $patient->birth_date->toDateString(),
                'phone_suffix' => $patient->phone === null ? null : Str::substr($patient->phone, -4),
                'status' => $patient->status,
            ])
            ->all();

        return new ToolResult(true, 'patients_found', 'Hasil pencarian pasien tersedia.', ['patients' => $patients]);
    }
}

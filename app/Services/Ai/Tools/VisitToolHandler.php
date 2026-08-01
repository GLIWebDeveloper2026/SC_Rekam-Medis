<?php

namespace App\Services\Ai\Tools;

use App\Data\Ai\ChatActorContext;
use App\Data\Ai\ToolResult;
use App\Models\Patient;
use App\Models\Visit;
use App\Services\AuditTrail;
use Illuminate\Support\Facades\Validator;

class VisitToolHandler
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    /** @param array<string, mixed> $arguments */
    public function execute(ChatActorContext $actor, string $toolName, array $arguments): ToolResult
    {
        if ($toolName === 'list_own_visit_history') {
            if (! $actor->isApprovedPatient()) {
                return new ToolResult(false, 'forbidden', 'Tool ini hanya tersedia untuk pasien yang disetujui.');
            }

            return $this->history($actor, $actor->patient);
        }

        if ($toolName === 'get_patient_visit_history') {
            if (! $actor->can('patients.view')) {
                return new ToolResult(false, 'forbidden', 'Anda tidak memiliki izin melihat riwayat pasien.');
            }

            $validated = Validator::make($arguments, [
                'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            ])->validate();
            $patient = Patient::query()->findOrFail($validated['patient_id']);
            $this->auditTrail->record(
                'ai.patient_visit_history.viewed',
                'patient',
                $patient->id,
                'success',
                $actor->user,
                $patient->id,
            );

            return $this->history($actor, $patient);
        }

        return new ToolResult(false, 'unknown_tool', 'Tool riwayat kunjungan tidak dikenal.');
    }

    private function history(ChatActorContext $actor, Patient $patient): ToolResult
    {
        $visits = Visit::query()
            ->select(['id', 'patient_id', 'registration_id', 'visit_date', 'status', 'arrived_at', 'completed_at'])
            ->whereBelongsTo($patient)
            ->with([
                'registration:id,patient_id,provider_schedule_id,requested_service,booking_code',
                'registration.schedule:id,provider_user_id,service_type',
                'registration.schedule.provider:id,name',
            ])
            ->latest('visit_date')
            ->limit(20)
            ->get()
            ->map(fn (Visit $visit): array => [
                'date' => $visit->visit_date->toDateString(),
                'service' => $visit->registration->requested_service,
                'provider' => $visit->registration->schedule?->provider?->name,
                'status' => $visit->status,
                'booking_code' => $visit->registration->booking_code,
                'arrived_at' => $visit->arrived_at?->toIso8601String(),
                'completed_at' => $visit->completed_at?->toIso8601String(),
            ])
            ->all();

        return new ToolResult(
            true,
            'visit_history_found',
            'Ringkasan riwayat kunjungan ditemukan.',
            ['visits' => $visits],
            'patient',
            $patient->id,
        );
    }
}

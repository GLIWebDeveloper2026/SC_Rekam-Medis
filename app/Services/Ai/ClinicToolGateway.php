<?php

namespace App\Services\Ai;

use App\Data\Ai\ChatActorContext;
use App\Data\Ai\ToolResult;
use App\Models\AiToolExecution;
use App\Services\Ai\Tools\AppointmentToolHandler;
use App\Services\Ai\Tools\PatientSearchToolHandler;
use App\Services\Ai\Tools\QueueToolHandler;
use App\Services\Ai\Tools\RegistrationToolHandler;
use App\Services\Ai\Tools\ScheduleToolHandler;
use App\Services\Ai\Tools\VisitToolHandler;
use App\Services\AuditTrail;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClinicToolGateway
{
    public function __construct(
        private readonly ClinicToolRegistry $registry,
        private readonly MutationIntentGuard $intentGuard,
        private readonly ScheduleToolHandler $scheduleTools,
        private readonly QueueToolHandler $queueTools,
        private readonly VisitToolHandler $visitTools,
        private readonly PatientSearchToolHandler $patientSearchTools,
        private readonly AppointmentToolHandler $appointmentTools,
        private readonly RegistrationToolHandler $registrationTools,
        private readonly AuditTrail $auditTrail,
    ) {}

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{result: ToolResult, execution_id: ?string}
     */
    public function execute(
        ChatActorContext $actor,
        string $toolName,
        array $arguments,
        string $latestUserMessage,
        string $clientIdempotencyKey,
    ): array {
        $allowedToolNames = collect($this->registry->forActor($actor))->pluck('name');

        if (! $allowedToolNames->contains($toolName)) {
            return ['result' => new ToolResult(false, 'forbidden', 'Tool tidak tersedia untuk akun ini.'), 'execution_id' => null];
        }

        if ($this->registry->isMutation($toolName) && ! $this->intentGuard->allows($toolName, $latestUserMessage)) {
            return ['result' => new ToolResult(false, 'explicit_intent_required', 'Tindakan hanya dapat dilakukan setelah diminta secara jelas.'), 'execution_id' => null];
        }

        $fingerprint = hash('sha256', json_encode($this->canonicalize($arguments), JSON_THROW_ON_ERROR));
        $executionKey = hash('sha256', implode('|', [$actor->user->id, $clientIdempotencyKey, $toolName]));
        $execution = AiToolExecution::query()->firstOrCreate(
            ['idempotency_key' => $executionKey],
            [
                'user_id' => $actor->user->id,
                'patient_id' => $actor->patient?->id,
                'active_role' => $actor->activeRole,
                'tool_name' => $toolName,
                'request_fingerprint' => $fingerprint,
                'status' => AiToolExecution::StatusPending,
                'safe_input_json' => $this->redactInput($toolName, $arguments),
                'started_at' => now(),
                'expires_at' => now()->addDay(),
            ],
        );

        if ($execution->request_fingerprint !== $fingerprint) {
            return ['result' => new ToolResult(false, 'idempotency_conflict', 'Kunci permintaan sudah digunakan dengan data berbeda.'), 'execution_id' => $execution->id];
        }

        if (! $execution->wasRecentlyCreated && $execution->status === AiToolExecution::StatusSucceeded) {
            return ['result' => $this->resultFromStored($execution), 'execution_id' => $execution->id];
        }

        if (! $execution->wasRecentlyCreated && $execution->status === AiToolExecution::StatusPending && ! $execution->expires_at->isPast()) {
            return ['result' => new ToolResult(false, 'execution_in_progress', 'Permintaan yang sama sedang diproses.'), 'execution_id' => $execution->id];
        }

        try {
            $result = $this->dispatch($actor, $toolName, $arguments);
            $storedOutput = [
                ...$result->toArray(),
                'resource_type' => $result->resourceType,
                'resource_id' => $result->resourceId,
            ];
            $execution->update([
                'status' => $result->ok ? AiToolExecution::StatusSucceeded : AiToolExecution::StatusFailed,
                'resource_type' => $result->resourceType,
                'resource_id' => $result->resourceId,
                'safe_output_json' => $storedOutput,
                'failure_code' => $result->ok ? null : $result->code,
                'failure_summary' => $result->ok ? null : $result->message,
                'completed_at' => now(),
            ]);
            $this->auditTrail->record(
                'ai.tool.executed',
                $result->resourceType ?? 'ai_tool_execution',
                $result->resourceId ?? $execution->id,
                $result->ok ? 'success' : 'failure',
                $actor->user,
                $actor->patient?->id,
                $result->ok ? null : $result->code,
                ['execution_id' => $execution->id, 'tool_name' => $toolName],
            );

            return ['result' => $result, 'execution_id' => $execution->id];
        } catch (Throwable $exception) {
            report($exception);
            $message = $exception instanceof ValidationException
                ? collect($exception->errors())->flatten()->first() ?? 'Data permintaan tidak valid.'
                : 'Tindakan tidak dapat diselesaikan.';
            $result = new ToolResult(false, 'tool_failed', $message);
            $execution->update([
                'status' => AiToolExecution::StatusFailed,
                'failure_code' => 'tool_failed',
                'failure_summary' => $message,
                'safe_output_json' => $result->toArray(),
                'completed_at' => now(),
            ]);

            return ['result' => $result, 'execution_id' => $execution->id];
        }
    }

    /** @param array<string, mixed> $arguments */
    private function dispatch(ChatActorContext $actor, string $toolName, array $arguments): ToolResult
    {
        return match ($toolName) {
            'list_public_schedules', 'find_available_slots' => $this->scheduleTools->execute($actor, $toolName, $arguments),
            'get_own_appointments', 'create_own_appointment', 'reschedule_own_appointment',
            'cancel_own_appointment', 'check_in_own_appointment', 'create_patient_appointment',
            'reschedule_patient_appointment', 'cancel_patient_appointment', 'check_in_patient' => $this->appointmentTools->execute($actor, $toolName, $arguments),
            'get_own_queue_status', 'get_queue_board' => $this->queueTools->execute($actor, $toolName, $arguments),
            'list_own_visit_history', 'get_patient_visit_history' => $this->visitTools->execute($actor, $toolName, $arguments),
            'search_patients' => $this->patientSearchTools->execute($actor, $arguments),
            'register_patient' => $this->registrationTools->execute($actor, $arguments),
            default => throw new DomainException('Unknown AI tool.'),
        };
    }

    /** @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    private function redactInput(string $toolName, array $arguments): array
    {
        return match ($toolName) {
            'register_patient' => Arr::only($arguments, ['birth_date', 'sex']),
            'search_patients' => ['query_hash' => hash('sha256', (string) ($arguments['query'] ?? ''))],
            'cancel_own_appointment', 'cancel_patient_appointment' => Arr::only($arguments, ['appointment_id']),
            default => Arr::except($arguments, ['nik', 'phone', 'address', 'guardian_name', 'guardian_phone', 'cancellation_reason']),
        };
    }

    /** @param array<string, mixed> $value
     * @return array<string, mixed>
     */
    private function canonicalize(array $value): array
    {
        ksort($value);

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->canonicalize($item);
            }
        }

        return $value;
    }

    private function resultFromStored(AiToolExecution $execution): ToolResult
    {
        $stored = $execution->safe_output_json ?? [];

        return new ToolResult(
            (bool) ($stored['ok'] ?? false),
            (string) ($stored['code'] ?? 'stored_result'),
            (string) ($stored['message'] ?? 'Hasil tersimpan ditemukan.'),
            is_array($stored['data'] ?? null) ? $stored['data'] : [],
            $stored['resource_type'] ?? $execution->resource_type,
            $stored['resource_id'] ?? $execution->resource_id,
        );
    }
}

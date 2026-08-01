<?php

namespace App\Services\Ai\Tools;

use App\Data\Ai\ChatActorContext;
use App\Data\Ai\ToolResult;
use App\Models\QueueTicket;
use Illuminate\Support\Facades\Validator;

class QueueToolHandler
{
    /** @param array<string, mixed> $arguments */
    public function execute(ChatActorContext $actor, string $toolName, array $arguments): ToolResult
    {
        return match ($toolName) {
            'get_own_queue_status' => $this->ownStatus($actor),
            'get_queue_board' => $this->board($actor, $arguments),
            default => new ToolResult(false, 'unknown_tool', 'Tool antrean tidak dikenal.'),
        };
    }

    private function ownStatus(ChatActorContext $actor): ToolResult
    {
        if (! $actor->isApprovedPatient()) {
            return new ToolResult(false, 'forbidden', 'Tool ini hanya tersedia untuk pasien yang disetujui.');
        }

        $ticket = QueueTicket::query()
            ->select(['id', 'registration_id', 'service_type', 'queue_number', 'status', 'checked_in_at'])
            ->with('registration:id,patient_id,booking_code')
            ->whereHas('registration', fn ($query) => $query->where('patient_id', $actor->patient->id))
            ->whereDate('service_date', now(config('clinic.timezone'))->toDateString())
            ->whereIn('status', ['booked', 'waiting', 'triaged', 'called'])
            ->latest('checked_in_at')
            ->first();

        if ($ticket === null) {
            return new ToolResult(true, 'queue_empty', 'Anda belum memiliki antrean aktif hari ini.');
        }

        return new ToolResult(true, 'queue_found', 'Status antrean Anda ditemukan.', [
            'booking_code' => $ticket->registration->booking_code,
            'queue_number' => $ticket->queue_number,
            'service' => $ticket->service_type,
            'status' => $ticket->status,
            'checked_in_at' => $ticket->checked_in_at?->toIso8601String(),
        ], 'queue_ticket', $ticket->id);
    }

    /** @param array<string, mixed> $arguments */
    private function board(ChatActorContext $actor, array $arguments): ToolResult
    {
        if (! $actor->can('queue.view')) {
            return new ToolResult(false, 'forbidden', 'Anda tidak memiliki izin melihat antrean.');
        }

        $validated = Validator::make($arguments, [
            'service_date' => ['required', 'date_format:Y-m-d'],
        ])->validate();
        $tickets = QueueTicket::query()
            ->select(['id', 'registration_id', 'service_type', 'queue_number', 'current_priority', 'status'])
            ->with([
                'registration:id,patient_id,booking_code',
                'registration.patient:id,full_name,medical_record_number',
            ])
            ->whereDate('service_date', $validated['service_date'])
            ->orderBy('queue_number')
            ->limit(100)
            ->get()
            ->map(fn (QueueTicket $ticket): array => [
                'queue_number' => $ticket->queue_number,
                'booking_code' => $ticket->registration->booking_code,
                'patient_name' => $ticket->registration->patient->full_name,
                'medical_record_number' => $ticket->registration->patient->medical_record_number,
                'service' => $ticket->service_type,
                'priority' => $ticket->current_priority,
                'status' => $ticket->status,
            ])
            ->all();

        return new ToolResult(true, 'queue_board_found', 'Data antrean ditemukan.', ['tickets' => $tickets]);
    }
}

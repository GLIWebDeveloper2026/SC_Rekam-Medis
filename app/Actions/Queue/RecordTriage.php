<?php

namespace App\Actions\Queue;

use App\Models\QueueTicket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecordTriage
{
    /** @param array<string, mixed> $data */
    public function execute(QueueTicket $ticket, array $data, string $userId): void
    {
        DB::transaction(function () use ($ticket, $data, $userId): void {
            $ticket = QueueTicket::query()->whereKey($ticket)->lockForUpdate()->firstOrFail();
            $visit = $ticket->registration()->with('visit')->firstOrFail()->visit;

            if ($visit === null) {
                throw ValidationException::withMessages(['queue_ticket' => 'Pasien harus check-in sebelum triage.']);
            }

            $previousHash = DB::table('triage_records')->where('visit_id', $visit->id)->latest('recorded_at')->value('integrity_hash');
            $recordedAt = now();
            $payload = [
                'visit_id' => $visit->id,
                'queue_ticket_id' => $ticket->id,
                'chief_complaint' => $data['chief_complaint'],
                'priority_level' => $data['priority_level'],
                'priority_reason' => $data['priority_reason'],
                'recorded_by' => $userId,
                'recorded_at' => $recordedAt->format('Y-m-d H:i:s.u'),
                'previous_hash' => $previousHash,
            ];
            DB::table('triage_records')->insert([
                'id' => (string) Str::uuid(),
                ...$payload,
                'recorded_at' => $recordedAt,
                'finalized_at' => $recordedAt,
                'integrity_hash' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
            ]);

            $vitals = collect(['temperature', 'blood_pressure_systolic', 'blood_pressure_diastolic', 'pulse', 'respiratory_rate', 'weight', 'height'])
                ->mapWithKeys(fn (string $key) => [$key => $data[$key] ?? null])
                ->all();
            if (collect($vitals)->filter(fn ($value) => $value !== null)->isNotEmpty()) {
                DB::table('vital_sign_entries')->insert([
                    'id' => (string) Str::uuid(),
                    'visit_id' => $visit->id,
                    'encounter_id' => null,
                    ...$vitals,
                    'recorded_by' => $userId,
                    'recorded_at' => $recordedAt,
                    'integrity_hash' => hash('sha256', json_encode([$visit->id, $vitals, $userId, $recordedAt->format('Y-m-d H:i:s.u')], JSON_THROW_ON_ERROR)),
                ]);
            }

            DB::table('queue_events')->insert([
                'id' => (string) Str::uuid(),
                'queue_ticket_id' => $ticket->id,
                'event_type' => $ticket->current_priority === $data['priority_level'] ? 'triaged' : 'priority_overridden',
                'old_status' => $ticket->status,
                'new_status' => 'triaged',
                'old_priority' => $ticket->current_priority,
                'new_priority' => $data['priority_level'],
                'reason' => $data['priority_reason'],
                'performed_by' => $userId,
                'created_at' => $recordedAt,
            ]);
            $ticket->update(['status' => 'triaged', 'current_priority' => $data['priority_level']]);
        }, attempts: 5);
    }
}

<?php

namespace App\Services\Ai;

use App\Data\Ai\ChatActorContext;

class ClinicToolRegistry
{
    /** @return array<int, array<string, mixed>> */
    public function forActor(ChatActorContext $actor): array
    {
        $tools = [
            $this->functionTool('list_public_schedules', 'List active public clinic schedules.', [], []),
            $this->functionTool('find_available_slots', 'Find available appointment slots for a schedule and date.', [
                'provider_schedule_id' => $this->string('UUID jadwal tenaga medis.'),
                'appointment_date' => $this->date('Tanggal kunjungan dalam format YYYY-MM-DD.'),
            ]),
        ];

        if ($actor->isApprovedPatient()) {
            return [
                ...$tools,
                $this->functionTool('get_own_appointments', 'List the authenticated patient appointments.', [], []),
                $this->functionTool('get_own_queue_status', 'Get the authenticated patient queue status for today.', [], []),
                $this->functionTool('list_own_visit_history', 'List summarized visit history for the authenticated patient.', [], []),
                $this->functionTool('create_own_appointment', 'Create an appointment for the authenticated patient.', $this->appointmentCreateProperties()),
                $this->functionTool('reschedule_own_appointment', 'Reschedule an owned future appointment.', $this->appointmentRescheduleProperties()),
                $this->functionTool('cancel_own_appointment', 'Cancel an owned future appointment.', [
                    'appointment_id' => $this->string('UUID janji temu.'),
                    'cancellation_reason' => $this->string('Alasan pembatalan.'),
                ]),
                $this->functionTool('check_in_own_appointment', 'Check in an owned appointment for today.', [
                    'appointment_id' => $this->string('UUID janji temu.'),
                ]),
            ];
        }

        if ($actor->can('queue.view')) {
            $tools[] = $this->functionTool('get_queue_board', 'List the operational queue board for one date.', [
                'service_date' => $this->date('Tanggal antrean dalam format YYYY-MM-DD.'),
            ]);
        }

        if ($actor->can('patients.view')) {
            $tools[] = $this->functionTool('search_patients', 'Search patient demographic records for authorized staff.', [
                'query' => $this->string('Nama, nomor rekam medis, atau nomor HP.'),
            ]);
            $tools[] = $this->functionTool('get_patient_visit_history', 'List summarized visit history for one patient.', [
                'patient_id' => $this->string('UUID pasien.'),
            ]);
        }

        if ($actor->can('patients.manage')) {
            $tools[] = $this->functionTool('register_patient', 'Register a new patient demographic record.', [
                'full_name' => $this->string('Nama lengkap pasien.'),
                'birth_date' => $this->date('Tanggal lahir YYYY-MM-DD.'),
                'sex' => $this->enum(['male', 'female', 'unknown'], 'Jenis kelamin.'),
                'phone' => $this->nullableString('Nomor HP atau null.'),
                'address' => $this->nullableString('Alamat atau null.'),
                'nik' => $this->nullableString('NIK 16 digit atau null.'),
                'guardian_name' => $this->nullableString('Nama wali atau null.'),
                'guardian_relationship' => $this->nullableString('Hubungan wali atau null.'),
                'guardian_phone' => $this->nullableString('Nomor HP wali atau null.'),
            ]);
        }

        if ($actor->can('queue.manage')) {
            $tools[] = $this->functionTool('create_patient_appointment', 'Create an appointment for a selected patient.', [
                'patient_id' => $this->string('UUID pasien.'),
                ...$this->appointmentCreateProperties(),
            ]);
            $tools[] = $this->functionTool('reschedule_patient_appointment', 'Reschedule a selected patient appointment.', $this->appointmentRescheduleProperties());
            $tools[] = $this->functionTool('cancel_patient_appointment', 'Cancel a selected patient appointment.', [
                'appointment_id' => $this->string('UUID janji temu.'),
                'cancellation_reason' => $this->string('Alasan pembatalan.'),
            ]);
            $tools[] = $this->functionTool('check_in_patient', 'Check in a selected patient appointment for today.', [
                'appointment_id' => $this->string('UUID janji temu.'),
            ]);
        }

        return $tools;
    }

    public function isMutation(string $toolName): bool
    {
        return in_array($toolName, [
            'create_own_appointment',
            'reschedule_own_appointment',
            'cancel_own_appointment',
            'check_in_own_appointment',
            'register_patient',
            'create_patient_appointment',
            'reschedule_patient_appointment',
            'cancel_patient_appointment',
            'check_in_patient',
        ], true);
    }

    /** @return array<string, array<string, mixed>> */
    private function appointmentCreateProperties(): array
    {
        return [
            'provider_schedule_id' => $this->string('UUID jadwal tenaga medis.'),
            'appointment_date' => $this->date('Tanggal janji temu YYYY-MM-DD.'),
            'slot_start' => $this->string('Waktu mulai HH:MM.'),
            'payer_type' => $this->enum(['general', 'insurance', 'other'], 'Jenis pembayaran.'),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function appointmentRescheduleProperties(): array
    {
        return [
            'appointment_id' => $this->string('UUID janji temu.'),
            'provider_schedule_id' => $this->string('UUID jadwal tenaga medis.'),
            'appointment_date' => $this->date('Tanggal baru YYYY-MM-DD.'),
            'slot_start' => $this->string('Waktu mulai baru HH:MM.'),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $properties
     * @param  array<int, string>|null  $required
     * @return array<string, mixed>
     */
    private function functionTool(
        string $name,
        string $description,
        array $properties,
        ?array $required = null,
    ): array {
        return [
            'type' => 'function',
            'name' => $name,
            'description' => $description,
            'strict' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required ?? array_keys($properties),
                'additionalProperties' => false,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function string(string $description): array
    {
        return ['type' => 'string', 'description' => $description];
    }

    /** @return array<string, mixed> */
    private function nullableString(string $description): array
    {
        return ['type' => ['string', 'null'], 'description' => $description];
    }

    /** @param array<int, string> $values
     * @return array<string, mixed>
     */
    private function enum(array $values, string $description): array
    {
        return ['type' => 'string', 'enum' => $values, 'description' => $description];
    }

    /** @return array<string, mixed> */
    private function date(string $description): array
    {
        return ['type' => 'string', 'format' => 'date', 'description' => $description];
    }
}

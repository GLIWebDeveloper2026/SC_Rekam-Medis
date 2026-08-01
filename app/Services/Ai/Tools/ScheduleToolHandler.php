<?php

namespace App\Services\Ai\Tools;

use App\Data\Ai\ChatActorContext;
use App\Data\Ai\ToolResult;
use App\Models\ProviderSchedule;
use App\Services\Appointments\AppointmentAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;

class ScheduleToolHandler
{
    public function __construct(private readonly AppointmentAvailability $availability) {}

    /** @param array<string, mixed> $arguments */
    public function execute(ChatActorContext $actor, string $toolName, array $arguments): ToolResult
    {
        return match ($toolName) {
            'list_public_schedules' => $this->listSchedules(),
            'find_available_slots' => $this->findSlots($arguments),
            default => new ToolResult(false, 'unknown_tool', 'Tool jadwal tidak dikenal.'),
        };
    }

    private function listSchedules(): ToolResult
    {
        $schedules = ProviderSchedule::query()
            ->select(['id', 'provider_user_id', 'service_type', 'day_of_week', 'start_time', 'end_time'])
            ->with('provider:id,name')
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', now())
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now()))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->limit(50)
            ->get()
            ->map(fn (ProviderSchedule $schedule): array => [
                'schedule_id' => $schedule->id,
                'provider' => $schedule->provider->name,
                'service' => $schedule->service_type,
                'weekday' => $schedule->day_of_week,
                'start_time' => substr($schedule->start_time, 0, 5),
                'end_time' => substr($schedule->end_time, 0, 5),
            ])
            ->all();

        return new ToolResult(true, 'schedules_found', 'Jadwal praktik aktif ditemukan.', ['schedules' => $schedules]);
    }

    /** @param array<string, mixed> $arguments */
    private function findSlots(array $arguments): ToolResult
    {
        $validated = Validator::make($arguments, [
            'provider_schedule_id' => ['required', 'uuid', 'exists:provider_schedules,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ])->validate();
        $schedule = ProviderSchedule::query()->with('provider:id,name')->findOrFail($validated['provider_schedule_id']);
        $date = CarbonImmutable::createFromFormat('Y-m-d', $validated['appointment_date'], config('clinic.timezone'));
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $dayName = $dayNames[$date->isoWeekday()] ?? 'hari tersebut';

        if (! $schedule->isAvailableOn($date)) {
            return new ToolResult(
                false,
                'doctor_not_practicing',
                "Dokter {$schedule->provider->name} tidak memiliki jadwal praktik pada hari {$dayName} ({$date->format('d-m-Y')}).",
                [
                    'schedule_id' => $schedule->id,
                    'doctor_name' => $schedule->provider->name,
                    'day_name' => $dayName,
                    'date' => $date->toDateString(),
                    'available_count' => 0,
                    'slots' => [],
                ],
            );
        }

        $allSlots = $this->availability->availableSlots($schedule, $date);
        $slots = $allSlots->take(20)->values()->all();

        return new ToolResult(
            true,
            $slots === [] ? 'no_slots' : 'slots_found',
            $slots === [] ? "Semua slot pada hari {$dayName} ({$date->format('d-m-Y')}) sudah penuh atau berlalu." : "Ditemukan {$allSlots->count()} slot tersedia untuk Dokter {$schedule->provider->name} pada hari {$dayName} ({$date->format('d-m-Y')}) dengan interval 30 menit.",
            [
                'schedule_id' => $schedule->id,
                'doctor_name' => $schedule->provider->name,
                'day_name' => $dayName,
                'date' => $date->toDateString(),
                'available_count' => $allSlots->count(),
                'slot_duration_minutes' => $schedule->slot_duration_minutes,
                'slots' => $slots,
            ],
        );
    }
}

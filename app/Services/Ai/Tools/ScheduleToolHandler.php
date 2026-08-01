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
        $schedule = ProviderSchedule::query()->findOrFail($validated['provider_schedule_id']);
        $date = CarbonImmutable::createFromFormat('Y-m-d', $validated['appointment_date'], config('clinic.timezone'));
        $slots = $this->availability->availableSlots($schedule, $date)->take(20)->values()->all();

        return new ToolResult(
            true,
            $slots === [] ? 'no_slots' : 'slots_found',
            $slots === [] ? 'Tidak ada slot yang tersedia.' : 'Slot tersedia ditemukan.',
            ['schedule_id' => $schedule->id, 'date' => $date->toDateString(), 'slots' => $slots],
        );
    }
}

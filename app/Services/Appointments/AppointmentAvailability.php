<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\ProviderSchedule;
use App\Models\ScheduleException;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AppointmentAvailability
{
    public function isAvailable(
        ProviderSchedule $schedule,
        CarbonInterface $date,
        string $slotStart,
        ?string $ignoreAppointmentId = null,
    ): bool {
        if (! $schedule->isAvailableOn($date)) {
            return false;
        }

        $hours = $this->effectiveHours($schedule, $date);

        if ($hours === null) {
            return false;
        }

        $timezone = config('clinic.timezone');
        $start = CarbonImmutable::parse($date->toDateString().' '.$slotStart, $timezone);
        $end = $start->addMinutes($schedule->slot_duration_minutes);

        if ($start->lt($hours['start']) || $end->gt($hours['end'])) {
            return false;
        }

        if ($date->isToday() && $start->lt(now($timezone))) {
            return false;
        }

        $minutesFromOpening = (int) $hours['start']->diffInMinutes($start);

        if ($minutesFromOpening % $schedule->slot_duration_minutes !== 0) {
            return false;
        }

        return Appointment::query()
            ->whereBelongsTo($schedule, 'schedule')
            ->whereDate('appointment_date', $date)
            ->whereTime('slot_start', $start->format('H:i:s'))
            ->whereIn('status', [Appointment::StatusBooked, Appointment::StatusCheckedIn])
            ->when(
                $ignoreAppointmentId !== null,
                fn ($query) => $query->whereKeyNot($ignoreAppointmentId),
            )
            ->count() < $schedule->slot_capacity;
    }

    /** @return Collection<int, array{start: string, end: string}> */
    public function availableSlots(ProviderSchedule $schedule, CarbonInterface $date): Collection
    {
        $hours = $this->effectiveHours($schedule, $date);

        if (! $schedule->isAvailableOn($date) || $hours === null) {
            return collect();
        }

        $slots = collect();
        $cursor = $hours['start'];

        while ($cursor->addMinutes($schedule->slot_duration_minutes)->lte($hours['end'])) {
            if ($this->isAvailable($schedule, $date, $cursor->format('H:i'))) {
                $slots->push([
                    'start' => $cursor->format('H:i'),
                    'end' => $cursor->addMinutes($schedule->slot_duration_minutes)->format('H:i'),
                ]);
            }

            $cursor = $cursor->addMinutes($schedule->slot_duration_minutes);
        }

        return $slots;
    }

    /** @return array{start: CarbonImmutable, end: CarbonImmutable}|null */
    public function effectiveHours(ProviderSchedule $schedule, CarbonInterface $date): ?array
    {
        $exception = ScheduleException::query()
            ->whereBelongsTo($schedule, 'schedule')
            ->whereDate('exception_date', $date)
            ->latest()
            ->first();

        if ($exception?->exception_type === 'closed') {
            return null;
        }

        $startTime = $exception?->replacement_start ?? $schedule->start_time;
        $endTime = $exception?->replacement_end ?? $schedule->end_time;

        if ($startTime === null || $endTime === null) {
            return null;
        }

        $timezone = config('clinic.timezone');

        return [
            'start' => CarbonImmutable::parse($date->toDateString().' '.$startTime, $timezone),
            'end' => CarbonImmutable::parse($date->toDateString().' '.$endTime, $timezone),
        ];
    }
}

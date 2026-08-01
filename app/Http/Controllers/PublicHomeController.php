<?php

namespace App\Http\Controllers;

use App\Models\ProviderSchedule;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(): View
    {
        $schedules = ProviderSchedule::query()
            ->select(['id', 'provider_user_id', 'service_type', 'day_of_week', 'start_time', 'end_time'])
            ->with('provider:id,name')
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', now())
            ->where(function ($query): void {
                $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now());
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (ProviderSchedule $schedule): string => implode('|', [
                $schedule->provider_user_id,
                $schedule->service_type,
                $schedule->start_time,
                $schedule->end_time,
            ]))
            ->map(function ($group): array {
                $schedule = $group->first();

                return [
                    'provider' => $schedule->provider->name,
                    'service' => $schedule->service_type,
                    'days' => $group->pluck('day_of_week')->sort()->values()->all(),
                    'start_time' => substr($schedule->start_time, 0, 5),
                    'end_time' => substr($schedule->end_time, 0, 5),
                ];
            })
            ->values();

        return view('welcome', ['schedules' => $schedules]);
    }
}

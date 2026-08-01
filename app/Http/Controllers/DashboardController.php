<?php

namespace App\Http\Controllers;

use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\QueueTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $metrics = [
            'queue_waiting' => QueueTicket::query()->whereDate('service_date', now()->toDateString())->whereIn('status', ['booked', 'waiting', 'triaged'])->count(),
            'active_encounters' => Encounter::query()->whereIn('status', ['planned', 'active'])->count(),
            'pharmacy_queue' => Prescription::query()->where('status', 'finalized')->count(),
            'patients' => Patient::query()->where('status', 'active')->count(),
        ];
        $priorityTickets = QueueTicket::query()
            ->with('registration.patient')
            ->whereDate('service_date', now()->toDateString())
            ->whereIn('current_priority', ['urgent', 'emergency'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact('metrics', 'priorityTickets'));
    }
}

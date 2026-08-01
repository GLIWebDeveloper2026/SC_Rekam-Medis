<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(): View
    {
        $tickets = QueueTicket::query()
            ->with('registration.patient')
            ->whereDate('service_date', now()->toDateString())
            ->orderByRaw("CASE current_priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 ELSE 3 END")
            ->orderBy('queue_number')
            ->get();

        return view('queue.index', compact('tickets'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\AuditTrail;
use App\Services\ClinicReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, ClinicReport $clinicReport, AuditTrail $auditTrail): View
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = ($request->date('end_date')?->addDay()->startOfDay()) ?? now()->addDay()->startOfDay();
        $report = $clinicReport->forPeriod($start, $end);
        $auditTrail->record('report.viewed', 'clinic_report', null, 'success', $request->user(), metadata: [
            'start_date' => $start->toDateString(),
            'end_date' => $end->copy()->subDay()->toDateString(),
        ]);

        return view('reports.index', compact('report', 'start', 'end'));
    }
}

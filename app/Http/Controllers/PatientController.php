<?php

namespace App\Http\Controllers;

use App\Actions\Patients\RegisterPatient;
use App\Http\Requests\Patients\StorePatientRequest;
use App\Models\Patient;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(private readonly AuditTrail $auditTrail) {}

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q'));
        $patients = Patient::query()
            ->when($query !== '', fn ($builder) => $builder->where(function ($inner) use ($query): void {
                $inner->where('full_name', 'like', "%{$query}%")
                    ->orWhere('medical_record_number', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients', 'query'));
    }

    public function create(): View
    {
        return view('patients.create');
    }

    public function store(StorePatientRequest $request, RegisterPatient $registerPatient): RedirectResponse
    {
        $patient = $registerPatient->execute($request->validated(), $request->user()->id);
        $this->auditTrail->record('patient.created', 'patient', $patient->id, 'success', $request->user(), $patient->id);

        return redirect()->route('patients.show', $patient)->with('status', 'Pasien berhasil didaftarkan.');
    }

    public function show(Request $request, Patient $patient): View
    {
        $patient->load(['identifiers', 'guardians']);
        $activeAllergies = $patient->allergyEntries()->where('clinical_status', 'active')->latest('recorded_at')->get();
        $canViewClinical = $request->user()->hasPermission('clinical.view') || $request->user()->hasPermission('pharmacy.manage');

        $this->auditTrail->record('patient.view', 'patient', $patient->id, 'success', $request->user(), $patient->id);

        return view('patients.show', compact('patient', 'activeAllergies', 'canViewClinical'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function index(): View
    {
        $prescriptions = Prescription::query()->with(['items', 'items.prescription'])->latest('finalized_at')->limit(30)->get();

        return view('pharmacy.index', compact('prescriptions'));
    }
}

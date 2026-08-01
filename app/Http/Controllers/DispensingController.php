<?php

namespace App\Http\Controllers;

use App\Actions\Pharmacy\DispensePrescription;
use App\Models\Prescription;
use App\Services\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DispensingController extends Controller
{
    public function store(Request $request, DispensePrescription $dispense, AuditTrail $auditTrail): RedirectResponse
    {
        $data = $request->validate([
            'prescription_id' => ['required', 'uuid', 'exists:prescriptions,id'], 'recipient_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'], 'items.*.prescription_item_id' => ['required', 'uuid', 'exists:prescription_items,id'],
            'items.*.medicine_batch_id' => ['required', 'uuid', 'exists:medicine_batches,id'], 'items.*.quantity_dispensed' => ['required', 'numeric', 'gt:0'],
        ]);
        $prescription = Prescription::query()->findOrFail($data['prescription_id']);
        $dispensing = $dispense->execute($prescription, $data['recipient_name'], $data['items'], $request->user());
        $auditTrail->record('dispensing.completed', 'dispensing', $dispensing->id, 'success', $request->user(), $prescription->patient_id);

        return redirect()->route('pharmacy.index')->with('status', 'Obat diserahkan dan stok batch diperbarui melalui movement.');
    }
}

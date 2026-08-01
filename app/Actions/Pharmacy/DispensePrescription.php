<?php

namespace App\Actions\Pharmacy;

use App\Models\Dispensing;
use App\Models\MedicineBatch;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DispensePrescription
{
    /** @param array<int, array<string, mixed>> $items */
    public function execute(Prescription $prescription, string $recipient, array $items, User $pharmacist): Dispensing
    {
        return DB::transaction(function () use ($prescription, $recipient, $items, $pharmacist): Dispensing {
            $validated = [];
            foreach ($items as $index => $row) {
                $item = PrescriptionItem::query()->where('prescription_id', $prescription->id)->findOrFail($row['prescription_item_id']);
                $batch = MedicineBatch::query()->whereKey($row['medicine_batch_id'])->lockForUpdate()->firstOrFail();
                if ($batch->expiry_date->isBefore(now()->startOfDay()) || $batch->status !== 'available') {
                    throw ValidationException::withMessages(["items.{$index}.medicine_batch_id" => 'Batch kedaluwarsa atau diblokir tidak dapat diserahkan.']);
                }
                if ($batch->medicine_id !== $item->medicine_id) {
                    throw ValidationException::withMessages(["items.{$index}.medicine_batch_id" => 'Batch tidak sesuai dengan item resep.']);
                }
                $available = (float) StockMovement::query()->where('medicine_batch_id', $batch->id)->sum('quantity');
                $quantity = (float) $row['quantity_dispensed'];
                if ($quantity <= 0 || $quantity > $available) {
                    throw ValidationException::withMessages(["items.{$index}.quantity_dispensed" => 'Jumlah penyerahan melebihi stok batch yang tersedia.']);
                }
                $validated[] = compact('item', 'batch', 'quantity');
            }

            $dispensedAt = now();
            $payload = ['prescription_id' => $prescription->id, 'patient_id' => $prescription->patient_id, 'dispensed_by' => $pharmacist->id, 'recipient_name' => $recipient, 'status' => 'dispensed', 'dispensed_at' => $dispensedAt->format('Y-m-d H:i:s.u')];
            $dispensing = Dispensing::query()->create([...$payload, 'integrity_hash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR))]);

            foreach ($validated as $row) {
                DB::table('dispensing_items')->insert([
                    'id' => (string) Str::uuid(), 'dispensing_id' => $dispensing->id,
                    'prescription_item_id' => $row['item']->id, 'medicine_batch_id' => $row['batch']->id,
                    'quantity_dispensed' => $row['quantity'], 'instruction_snapshot' => $row['item']->instruction,
                ]);
                $movementPayload = [
                    'medicine_batch_id' => $row['batch']->id, 'movement_type' => 'dispensing', 'quantity' => -$row['quantity'],
                    'reference_type' => 'dispensing', 'reference_id' => $dispensing->id, 'performed_by' => $pharmacist->id,
                    'reason' => 'Penyerahan resep '.$prescription->id, 'created_at' => $dispensedAt->format('Y-m-d H:i:s.u'),
                ];
                StockMovement::query()->create([...$movementPayload, 'integrity_hash' => hash('sha256', json_encode($movementPayload, JSON_THROW_ON_ERROR))]);
            }

            return $dispensing;
        }, attempts: 5);
    }
}

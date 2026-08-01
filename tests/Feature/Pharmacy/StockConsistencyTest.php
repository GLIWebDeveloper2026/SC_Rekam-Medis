<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Dispensing;
use App\Models\Encounter;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Registration;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\ClinicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispensing_uses_batch_movement_and_cannot_make_stock_negative(): void
    {
        [$pharmacist, $prescription, $item, $batch] = $this->context(expired: false);
        StockMovement::query()->create([
            'medicine_batch_id' => $batch->id, 'movement_type' => 'opening', 'quantity' => 10,
            'reference_type' => 'opening_stock', 'reference_id' => null, 'performed_by' => $pharmacist->id,
            'reason' => 'Stok awal terverifikasi', 'created_at' => now(), 'integrity_hash' => str_repeat('d', 64),
        ]);

        $this->actingAs($pharmacist)->post('/dispensings', [
            'prescription_id' => $prescription->id,
            'recipient_name' => 'Pasien sendiri',
            'items' => [['prescription_item_id' => $item->id, 'medicine_batch_id' => $batch->id, 'quantity_dispensed' => 3]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Dispensing::query()->count());
        $this->assertSame(7.0, (float) StockMovement::query()->where('medicine_batch_id', $batch->id)->sum('quantity'));

        $this->actingAs($pharmacist)->post('/dispensings', [
            'prescription_id' => $prescription->id,
            'recipient_name' => 'Pasien sendiri',
            'items' => [['prescription_item_id' => $item->id, 'medicine_batch_id' => $batch->id, 'quantity_dispensed' => 8]],
        ])->assertSessionHasErrors('items.0.quantity_dispensed');

        $this->assertSame(7.0, (float) StockMovement::query()->where('medicine_batch_id', $batch->id)->sum('quantity'));
    }

    public function test_expired_batch_cannot_be_dispensed(): void
    {
        [$pharmacist, $prescription, $item, $batch] = $this->context(expired: true);
        StockMovement::query()->create([
            'medicine_batch_id' => $batch->id, 'movement_type' => 'opening', 'quantity' => 10,
            'reference_type' => 'opening_stock', 'performed_by' => $pharmacist->id,
            'reason' => 'Stok awal', 'created_at' => now(), 'integrity_hash' => str_repeat('e', 64),
        ]);

        $this->actingAs($pharmacist)->post('/dispensings', [
            'prescription_id' => $prescription->id,
            'recipient_name' => 'Pasien sendiri',
            'items' => [['prescription_item_id' => $item->id, 'medicine_batch_id' => $batch->id, 'quantity_dispensed' => 1]],
        ])->assertSessionHasErrors('items.0.medicine_batch_id');

        $this->assertSame(0, Dispensing::query()->count());
    }

    /** @return array{User, Prescription, PrescriptionItem, MedicineBatch} */
    private function context(bool $expired): array
    {
        $this->seed(ClinicSeeder::class);
        $staff = User::query()->where('email', 'pendaftaran@sehatbersama.test')->firstOrFail();
        $doctor = User::query()->where('email', 'dokter@sehatbersama.test')->firstOrFail();
        $pharmacist = User::query()->where('email', 'apoteker@sehatbersama.test')->firstOrFail();
        $patient = Patient::query()->create(['medical_record_number' => 'RM-STOCK-001', 'full_name' => 'Pasien Stok', 'birth_date' => '1985-01-01', 'sex' => 'male', 'status' => 'active', 'created_by' => $staff->id]);
        $registration = Registration::query()->create(['patient_id' => $patient->id, 'registration_date' => now()->toDateString(), 'channel' => 'front_desk', 'payer_type' => 'general', 'requested_service' => 'general', 'status' => 'checked_in', 'booking_code' => 'BK-STOCK-001', 'created_by' => $staff->id]);
        $visit = Visit::query()->create(['patient_id' => $patient->id, 'registration_id' => $registration->id, 'visit_date' => now()->toDateString(), 'payer_type' => 'general', 'status' => 'active', 'arrived_at' => now()]);
        $encounter = Encounter::query()->create(['visit_id' => $visit->id, 'service_type' => 'general', 'responsible_provider_id' => $doctor->id, 'status' => 'finalized', 'started_at' => now(), 'finalized_at' => now()]);
        $medicine = Medicine::query()->create(['code' => 'MED-STOCK', 'generic_name' => 'Cetirizine', 'dosage_form' => 'tablet', 'strength' => '10 mg', 'unit' => 'tablet', 'status' => 'active']);
        $batch = MedicineBatch::query()->create(['medicine_id' => $medicine->id, 'batch_number' => 'BATCH-01', 'expiry_date' => $expired ? now()->subDay() : now()->addYear(), 'received_quantity' => 10, 'status' => 'available']);
        $prescription = Prescription::query()->create(['patient_id' => $patient->id, 'visit_id' => $visit->id, 'encounter_id' => $encounter->id, 'prescriber_id' => $doctor->id, 'status' => 'finalized', 'finalized_at' => now(), 'integrity_hash' => str_repeat('f', 64)]);
        $item = PrescriptionItem::query()->create(['prescription_id' => $prescription->id, 'medicine_id' => $medicine->id, 'medicine_name_snapshot' => $medicine->generic_name, 'strength_snapshot' => $medicine->strength, 'dosage' => '1 tablet', 'frequency' => '1 kali sehari', 'route' => 'oral', 'duration' => '3 hari', 'quantity' => 3, 'instruction' => 'Malam', 'preparation_type' => 'finished', 'integrity_hash' => str_repeat('1', 64)]);

        return [$pharmacist, $prescription, $item, $batch];
    }
}

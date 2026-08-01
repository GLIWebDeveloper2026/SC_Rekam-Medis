<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('generic_name')->index();
            $table->string('brand_name')->nullable()->index();
            $table->string('dosage_form', 80);
            $table->string('strength', 80);
            $table->string('unit', 40);
            $table->boolean('is_compound_component')->default(false);
            $table->string('status', 20)->default('active')->index();
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->timestamps(6);
        });

        Schema::create('medicine_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('medicine_id')->constrained()->restrictOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date')->index();
            $table->decimal('received_quantity', 12, 3);
            $table->string('status', 20)->default('available')->index();
            $table->timestamps(6);
            $table->unique(['medicine_id', 'batch_number']);
        });

        Schema::create('prescriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('visit_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('encounter_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('prescriber_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 30)->index();
            $table->dateTime('finalized_at', precision: 6)->index();
            $table->uuid('corrects_prescription_id')->nullable()->index();
            $table->text('cancellation_reason')->nullable();
            $table->char('integrity_hash', 64)->unique();
            $table->char('previous_hash', 64)->nullable();
            $table->foreign('corrects_prescription_id')->references('id')->on('prescriptions')->restrictOnDelete();
        });

        Schema::create('prescription_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('prescription_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('medicine_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('medicine_name_snapshot');
            $table->string('strength_snapshot')->nullable();
            $table->string('dosage');
            $table->string('frequency');
            $table->string('route', 80);
            $table->string('duration')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->text('instruction')->nullable();
            $table->string('preparation_type', 20)->default('finished');
            $table->char('integrity_hash', 64)->unique();
        });

        Schema::create('compound_formulas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('prescription_item_id')->unique()->constrained()->restrictOnDelete();
            $table->text('instructions');
            $table->decimal('final_quantity', 12, 3);
        });

        Schema::create('compound_components', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('compound_formula_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('medicine_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 40);
        });

        Schema::create('prescription_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('prescription_id')->constrained()->restrictOnDelete();
            $table->string('event_type', 40)->index();
            $table->foreignUuid('performed_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->dateTime('created_at', precision: 6)->index();
            $table->char('integrity_hash', 64)->unique();
            $table->char('previous_hash', 64)->nullable();
        });

        Schema::create('substitution_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('prescription_item_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('proposed_medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->text('reason');
            $table->foreignUuid('proposed_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 60)->default('proposed')->index();
            $table->dateTime('created_at', precision: 6);
        });

        Schema::create('substitution_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('substitution_request_id')->constrained()->restrictOnDelete();
            $table->string('event_type', 60)->index();
            $table->foreignUuid('doctor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignUuid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('communication_channel', 50)->nullable();
            $table->dateTime('verbal_approval_at', precision: 6)->nullable();
            $table->dateTime('ratified_at', precision: 6)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('created_at', precision: 6);
            $table->char('integrity_hash', 64)->unique();
        });

        Schema::create('dispensings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('prescription_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('patient_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('dispensed_by')->constrained('users')->restrictOnDelete();
            $table->string('recipient_name');
            $table->string('status', 30)->default('dispensed');
            $table->dateTime('dispensed_at', precision: 6);
            $table->char('integrity_hash', 64)->unique();
        });

        Schema::create('dispensing_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('dispensing_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('prescription_item_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('medicine_batch_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_dispensed', 12, 3);
            $table->text('instruction_snapshot')->nullable();
        });

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('medicine_batch_id')->constrained()->restrictOnDelete();
            $table->string('movement_type', 40)->index();
            $table->decimal('quantity', 12, 3);
            $table->string('reference_type', 80);
            $table->string('reference_id', 64)->nullable()->index();
            $table->foreignUuid('performed_by')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->dateTime('created_at', precision: 6)->index();
            $table->char('integrity_hash', 64)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('dispensing_items');
        Schema::dropIfExists('dispensings');
        Schema::dropIfExists('substitution_events');
        Schema::dropIfExists('substitution_requests');
        Schema::dropIfExists('prescription_events');
        Schema::dropIfExists('compound_components');
        Schema::dropIfExists('compound_formulas');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
    }
};

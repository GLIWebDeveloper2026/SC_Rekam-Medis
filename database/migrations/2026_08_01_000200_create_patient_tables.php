<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_counters', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps(6);
        });

        Schema::create('patients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('medical_record_number')->unique();
            $table->uuid('canonical_patient_id')->nullable()->index();
            $table->string('full_name');
            $table->string('normalized_name')->nullable()->index();
            $table->date('birth_date')->index();
            $table->string('sex', 20);
            $table->string('phone', 30)->nullable()->index();
            $table->text('address')->nullable();
            $table->dateTime('deceased_at', precision: 6)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps(6);
            $table->foreign('canonical_patient_id')->references('id')->on('patients')->nullOnDelete();
        });

        Schema::create('patient_identifiers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->string('identifier_type', 40)->index();
            $table->text('identifier_value');
            $table->char('normalized_hash', 64);
            $table->string('verified_status', 20)->default('unverified');
            $table->string('source')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->foreignUuid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('recorded_at', precision: 6);
            $table->unique(['identifier_type', 'normalized_hash']);
        });

        Schema::create('patient_demographic_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->json('old_value_json')->nullable();
            $table->json('new_value_json');
            $table->text('reason')->nullable();
            $table->foreignUuid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('recorded_at', precision: 6);
        });

        Schema::create('patient_guardians', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->uuid('guardian_patient_id')->nullable()->index();
            $table->string('guardian_name');
            $table->string('relationship', 50);
            $table->string('phone', 30)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps(6);
            $table->foreign('guardian_patient_id')->references('id')->on('patients')->nullOnDelete();
        });

        Schema::create('patient_aliases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->string('alias_name');
            $table->string('alias_type', 40)->default('alternate');
            $table->dateTime('recorded_at', precision: 6);
        });

        Schema::create('allergy_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->string('substance_code')->nullable();
            $table->string('substance_name');
            $table->text('reaction')->nullable();
            $table->string('severity', 20)->default('unknown');
            $table->string('clinical_status', 20)->default('active')->index();
            $table->string('verification_status', 20)->default('unconfirmed');
            $table->string('source')->nullable();
            $table->foreignUuid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('recorded_at', precision: 6);
            $table->uuid('supersedes_allergy_entry_id')->nullable()->index();
            $table->char('integrity_hash', 64)->unique();
            $table->foreign('supersedes_allergy_entry_id')->references('id')->on('allergy_entries')->nullOnDelete();
        });

        Schema::create('patient_merge_cases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('status', 24)->index();
            $table->foreignUuid('candidate_patient_a_id')->constrained('patients')->restrictOnDelete();
            $table->foreignUuid('candidate_patient_b_id')->constrained('patients')->restrictOnDelete();
            $table->text('reason');
            $table->json('evidence_json')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('decided_at', precision: 6)->nullable();
            $table->timestamps(6);
        });

        Schema::create('patient_merge_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('merge_case_id')->constrained('patient_merge_cases')->restrictOnDelete();
            $table->foreignUuid('canonical_patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignUuid('source_patient_id')->constrained('patients')->restrictOnDelete();
            $table->string('event_type', 20);
            $table->text('reason');
            $table->foreignUuid('performed_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('approved_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('created_at', precision: 6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_merge_events');
        Schema::dropIfExists('patient_merge_cases');
        Schema::dropIfExists('allergy_entries');
        Schema::dropIfExists('patient_aliases');
        Schema::dropIfExists('patient_guardians');
        Schema::dropIfExists('patient_demographic_events');
        Schema::dropIfExists('patient_identifiers');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('business_counters');
    }
};

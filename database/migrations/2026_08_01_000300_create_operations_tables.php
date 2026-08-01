<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_schedules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_user_id')->constrained('users')->restrictOnDelete();
            $table->string('service_type', 30)->index();
            $table->unsignedTinyInteger('day_of_week')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps(6);
        });

        Schema::create('schedule_exceptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_schedule_id')->constrained()->cascadeOnDelete();
            $table->date('exception_date')->index();
            $table->string('exception_type', 30);
            $table->time('replacement_start')->nullable();
            $table->time('replacement_end')->nullable();
            $table->text('reason')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps(6);
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('provider_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('registration_date')->index();
            $table->string('channel', 30);
            $table->string('payer_type', 30)->index();
            $table->string('coverage_id')->nullable();
            $table->string('requested_service', 30)->index();
            $table->string('status', 30)->default('booked')->index();
            $table->string('booking_code')->unique();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps(6);
        });

        Schema::create('appointments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('registration_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('provider_schedule_id')->constrained()->restrictOnDelete();
            $table->date('appointment_date')->index();
            $table->time('slot_start');
            $table->string('status', 30)->default('booked');
            $table->dateTime('booked_at', precision: 6);
        });

        Schema::create('daily_queue_counters', function (Blueprint $table): void {
            $table->date('service_date');
            $table->string('service_type', 30);
            $table->unsignedInteger('last_number')->default(0);
            $table->dateTime('updated_at', precision: 6);
            $table->primary(['service_date', 'service_type']);
        });

        Schema::create('queue_tickets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('registration_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('service_date')->index();
            $table->string('service_type', 30)->index();
            $table->unsignedInteger('queue_number');
            $table->unsignedInteger('original_position');
            $table->string('current_priority', 20)->default('routine')->index();
            $table->string('status', 30)->default('booked')->index();
            $table->dateTime('checked_in_at', precision: 6)->nullable();
            $table->timestamps(6);
            $table->unique(['service_date', 'service_type', 'queue_number']);
        });

        Schema::create('queue_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('queue_ticket_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 40)->index();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->string('old_priority', 20)->nullable();
            $table->string('new_priority', 20)->nullable();
            $table->text('reason')->nullable();
            $table->foreignUuid('performed_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('created_at', precision: 6);
        });

        Schema::create('visits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('registration_id')->unique()->constrained()->restrictOnDelete();
            $table->date('visit_date')->index();
            $table->string('payer_type', 30)->index();
            $table->string('status', 20)->default('active')->index();
            $table->dateTime('arrived_at', precision: 6);
            $table->dateTime('completed_at', precision: 6)->nullable();
            $table->timestamps(6);
        });

        Schema::create('triage_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('queue_ticket_id')->constrained()->restrictOnDelete();
            $table->text('chief_complaint');
            $table->string('priority_level', 20)->index();
            $table->text('priority_reason');
            $table->foreignUuid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('recorded_at', precision: 6);
            $table->dateTime('finalized_at', precision: 6);
            $table->char('integrity_hash', 64)->unique();
            $table->char('previous_hash', 64)->nullable();
        });

        Schema::create('vital_sign_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained()->restrictOnDelete();
            $table->uuid('encounter_id')->nullable()->index();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('blood_pressure_systolic')->nullable();
            $table->unsignedSmallInteger('blood_pressure_diastolic')->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('height', 6, 2)->nullable();
            $table->foreignUuid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('recorded_at', precision: 6);
            $table->char('integrity_hash', 64)->unique();
        });

        Schema::create('encounters', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained()->restrictOnDelete();
            $table->string('service_type', 30)->index();
            $table->foreignUuid('responsible_provider_id')->constrained('users')->restrictOnDelete();
            $table->uuid('referral_from_encounter_id')->nullable()->index();
            $table->string('status', 20)->default('planned')->index();
            $table->dateTime('started_at', precision: 6)->nullable();
            $table->dateTime('finalized_at', precision: 6)->nullable();
            $table->timestamps(6);
            $table->foreign('referral_from_encounter_id')->references('id')->on('encounters')->nullOnDelete();
        });

        Schema::table('vital_sign_entries', function (Blueprint $table): void {
            $table->foreign('encounter_id')->references('id')->on('encounters')->nullOnDelete();
        });

        Schema::create('encounter_participants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->restrictOnDelete();
            $table->string('participant_role', 50);
            $table->dateTime('joined_at', precision: 6);
            $table->dateTime('left_at', precision: 6)->nullable();
        });

        Schema::create('referrals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_encounter_id')->constrained('encounters')->restrictOnDelete();
            $table->string('target_service', 30);
            $table->foreignUuid('target_provider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority', 20)->default('routine');
            $table->text('reason');
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('created_at', precision: 6);
            $table->string('status', 20)->default('planned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('encounter_participants');
        Schema::table('vital_sign_entries', fn (Blueprint $table) => $table->dropForeign(['encounter_id']));
        Schema::dropIfExists('encounters');
        Schema::dropIfExists('vital_sign_entries');
        Schema::dropIfExists('triage_records');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('queue_events');
        Schema::dropIfExists('queue_tickets');
        Schema::dropIfExists('daily_queue_counters');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('schedule_exceptions');
        Schema::dropIfExists('provider_schedules');
    }
};

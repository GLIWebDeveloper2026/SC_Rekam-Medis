<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_record_copy_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->restrictOnDelete();
            $table->string('requester_name');
            $table->string('requester_relationship', 50);
            $table->text('purpose');
            $table->date('requested_period_start');
            $table->date('requested_period_end');
            $table->text('requested_scope');
            $table->string('status', 40)->default('submitted')->index();
            $table->foreignUuid('identity_verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->text('approval_notes')->nullable();
            $table->dateTime('approved_at', precision: 6)->nullable();
            $table->dateTime('released_at', precision: 6)->nullable();
            $table->timestamps(6);
        });

        Schema::create('generated_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('document_type', 50)->index();
            $table->foreignUuid('patient_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('source_request_id')->unique()->constrained('medical_record_copy_requests')->restrictOnDelete();
            $table->string('storage_key');
            $table->string('document_number')->unique();
            $table->char('checksum', 64);
            $table->string('watermark_text');
            $table->foreignUuid('generated_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('generated_at', precision: 6);
            $table->dateTime('expires_at', precision: 6)->nullable();
        });

        Schema::create('document_access_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('generated_documents')->restrictOnDelete();
            $table->string('event_type', 40)->index();
            $table->foreignUuid('performed_by')->constrained('users')->restrictOnDelete();
            $table->string('recipient')->nullable();
            $table->text('reason')->nullable();
            $table->dateTime('created_at', precision: 6)->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_access_events');
        Schema::dropIfExists('generated_documents');
        Schema::dropIfExists('medical_record_copy_requests');
    }
};

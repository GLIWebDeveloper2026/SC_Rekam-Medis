<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_drafts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('author_id')->constrained('users')->restrictOnDelete();
            $table->string('entry_type', 50);
            $table->json('content_json');
            $table->dateTime('expires_at', precision: 6)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps(6);
            $table->index(['encounter_id', 'author_id', 'status']);
        });

        Schema::create('clinical_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('visit_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('encounter_id')->constrained()->restrictOnDelete();
            $table->string('entry_type', 50)->index();
            $table->json('content_json');
            $table->foreignUuid('author_id')->constrained('users')->restrictOnDelete();
            $table->string('author_role', 50);
            $table->dateTime('clinical_time', precision: 6);
            $table->dateTime('recorded_at', precision: 6)->index();
            $table->dateTime('finalized_at', precision: 6)->index();
            $table->uuid('supersedes_entry_id')->nullable()->index();
            $table->text('correction_reason')->nullable();
            $table->string('entry_status', 30)->default('original')->index();
            $table->char('integrity_hash', 64)->unique();
            $table->char('previous_hash', 64)->nullable();
            $table->foreign('supersedes_entry_id')->references('id')->on('clinical_entries')->restrictOnDelete();
        });

        Schema::create('clinical_entry_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_entry_id')->constrained('clinical_entries')->restrictOnDelete();
            $table->foreignUuid('target_entry_id')->constrained('clinical_entries')->restrictOnDelete();
            $table->string('link_type', 40);
            $table->dateTime('created_at', precision: 6);
        });

        Schema::create('diagnosis_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('clinical_entry_id')->constrained()->restrictOnDelete();
            $table->string('diagnosis_code', 40)->index();
            $table->string('diagnosis_name');
            $table->string('diagnosis_type', 30)->default('primary');
            $table->boolean('is_primary')->default(false)->index();
        });

        Schema::create('procedure_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('clinical_entry_id')->constrained()->restrictOnDelete();
            $table->string('procedure_code', 40)->nullable()->index();
            $table->string('procedure_name');
            $table->decimal('quantity', 10, 2)->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_entries');
        Schema::dropIfExists('diagnosis_entries');
        Schema::dropIfExists('clinical_entry_links');
        Schema::dropIfExists('clinical_entries');
        Schema::dropIfExists('clinical_drafts');
    }
};

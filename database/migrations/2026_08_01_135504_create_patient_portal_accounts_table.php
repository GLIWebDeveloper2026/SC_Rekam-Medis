<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patient_portal_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('patient_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->date('claimed_birth_date');
            $table->string('claimed_phone', 30);
            $table->string('claimed_medical_record_number')->nullable()->index();
            $table->char('claimed_identifier_hash', 64)->nullable()->index();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at', precision: 6)->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps(6);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_portal_accounts');
    }
};

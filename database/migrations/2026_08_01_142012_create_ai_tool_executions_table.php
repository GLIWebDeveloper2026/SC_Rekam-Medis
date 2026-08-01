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
        Schema::create('ai_tool_executions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('idempotency_key')->unique();
            $table->foreignUuid('user_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('active_role', 50)->nullable()->index();
            $table->string('tool_name', 100)->index();
            $table->char('request_fingerprint', 64)->index();
            $table->string('status', 20)->default('pending')->index();
            $table->string('resource_type', 80)->nullable()->index();
            $table->string('resource_id', 64)->nullable()->index();
            $table->json('safe_input_json')->nullable();
            $table->json('safe_output_json')->nullable();
            $table->string('failure_code', 80)->nullable();
            $table->text('failure_summary')->nullable();
            $table->dateTime('started_at', precision: 6);
            $table->dateTime('completed_at', precision: 6)->nullable();
            $table->dateTime('expires_at', precision: 6)->index();
            $table->timestamps(6);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_tool_executions');
    }
};

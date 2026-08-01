<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps(6);
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('resource', 64)->index();
            $table->string('action', 64)->index();
            $table->timestamps(6);
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->foreignUuid('role_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('role_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('valid_from', precision: 6)->nullable();
            $table->dateTime('valid_until', precision: 6)->nullable();
            $table->dateTime('assigned_at', precision: 6);
            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('staff_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('staff_number')->unique();
            $table->string('full_name');
            $table->string('profession', 80);
            $table->string('license_number')->nullable();
            $table->string('service_unit', 80)->nullable();
            $table->date('active_from')->nullable();
            $table->date('active_until')->nullable();
            $table->timestamps(6);
        });

        Schema::create('audit_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->dateTime('occurred_at', precision: 6)->index();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('active_role', 50)->nullable();
            $table->string('action', 100)->index();
            $table->string('resource_type', 80)->index();
            $table->string('resource_id', 64)->nullable()->index();
            $table->uuid('patient_id')->nullable()->index();
            $table->string('result', 20)->index();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->json('metadata_json')->nullable();
            $table->char('previous_hash', 64)->nullable();
            $table->char('integrity_hash', 64)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};

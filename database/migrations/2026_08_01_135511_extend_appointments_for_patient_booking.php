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
        Schema::table('appointments', function (Blueprint $table): void {
            $table->time('slot_end')->after('slot_start');
            $table->foreignUuid('rescheduled_from_id')->nullable()->after('status')->constrained('appointments')->nullOnDelete();
            $table->foreignUuid('cancelled_by')->nullable()->after('rescheduled_from_id')->constrained('users')->nullOnDelete();
            $table->dateTime('cancelled_at', precision: 6)->nullable()->after('cancelled_by');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->timestamps(6);
            $table->index(
                ['provider_schedule_id', 'appointment_date', 'slot_start', 'status'],
                'appointments_slot_lookup_index',
            );
        });

        Schema::table('provider_schedules', function (Blueprint $table): void {
            $table->unsignedSmallInteger('slot_duration_minutes')->default(30)->after('end_time');
            $table->unsignedSmallInteger('slot_capacity')->default(1)->after('slot_duration_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_schedules', function (Blueprint $table): void {
            $table->dropColumn(['slot_duration_minutes', 'slot_capacity']);
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex('appointments_slot_lookup_index');
            $table->dropForeign(['rescheduled_from_id']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'slot_end',
                'rescheduled_from_id',
                'cancelled_by',
                'cancelled_at',
                'cancellation_reason',
                'created_at',
                'updated_at',
            ]);
        });
    }
};

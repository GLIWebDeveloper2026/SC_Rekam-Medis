<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = ['clinical_entries', 'audit_events', 'allergy_entries', 'triage_records'];

    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach ($this->tables as $table) {
            DB::unprepared("CREATE TRIGGER trg_{$table}_block_update BEFORE UPDATE ON {$table} FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Final record is immutable'");
            DB::unprepared("CREATE TRIGGER trg_{$table}_block_delete BEFORE DELETE ON {$table} FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Final record is immutable'");
        }
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach ($this->tables as $table) {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_{$table}_block_update");
            DB::unprepared("DROP TRIGGER IF EXISTS trg_{$table}_block_delete");
        }
    }
};

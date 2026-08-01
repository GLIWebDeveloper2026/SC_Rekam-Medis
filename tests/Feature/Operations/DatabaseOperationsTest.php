<?php

namespace Tests\Feature\Operations;

use Tests\TestCase;

class DatabaseOperationsTest extends TestCase
{
    public function test_database_operations_are_documented_and_scripted(): void
    {
        $databaseGuide = $this->projectFile('docs/operations/database.md');
        $backupGuide = $this->projectFile('docs/operations/backup-and-restore.md');
        $backupScript = $this->projectFile('scripts/backup-mariadb.ps1');
        $restoreScript = $this->projectFile('scripts/verify-restore.ps1');
        $readme = $this->projectFile('README.md');

        foreach (['MariaDB 10.11', 'clinic_app', 'clinic_migrator', 'clinic_report', 'clinic_backup', 'Asia/Jakarta', 'InnoDB', 'utf8mb4', 'FILESYSTEM_DISK=private', 'SESSION_DRIVER=redis'] as $requirement) {
            $this->assertStringContainsString($requirement, $databaseGuide);
        }

        foreach (['age', '35 hari', 'restore drill', 'point-in-time recovery', 'audit trail'] as $requirement) {
            $this->assertStringContainsStringIgnoringCase($requirement, $backupGuide);
        }

        foreach (['SupportsShouldProcess', '--defaults-extra-file', '--single-transaction', 'Get-FileHash', 'RetentionDays', 'AgeRecipient'] as $requirement) {
            $this->assertStringContainsString($requirement, $backupScript);
        }

        foreach (['SupportsShouldProcess', 'ChecksumFile', 'AgeIdentityFile', 'RestoreDatabase', 'CREATE DATABASE', 'DROP DATABASE'] as $requirement) {
            $this->assertStringContainsString($requirement, $restoreScript);
        }

        $this->assertStringContainsString('docs/operations/database.md', $readme);
        $this->assertStringContainsString('docs/operations/backup-and-restore.md', $readme);
    }

    private function projectFile(string $path): string
    {
        $contents = @file_get_contents(base_path($path));

        $this->assertIsString($contents, "File operasional {$path} belum tersedia.");

        return $contents;
    }
}

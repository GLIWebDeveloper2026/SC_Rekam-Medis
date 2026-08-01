# Operasi Database Klinik

Dokumen ini adalah baseline deployment database Sistem Informasi Klinik Pratama Sehat Bersama. Akses administrasi dan verifikasi dilakukan melalui DBHub; aplikasi tetap memakai koneksi Laravel dengan akun runtime yang dibatasi.

## Release gate

- Produksi wajib memakai MariaDB 10.11 dengan InnoDB. Jalankan `SELECT VERSION()` dan pastikan hasil memuat `10.11` serta `MariaDB` sebelum rilis.
- Database `medis` wajib memakai `utf8mb4` dan `utf8mb4_unicode_ci`.
- Zona waktu aplikasi adalah `Asia/Jakarta`; setiap koneksi database aplikasi menjalankan `SET time_zone = '+07:00'`.
- Seluruh migrasi harus berstatus `Ran`, trigger immutable harus tersedia, dan akun demo tidak boleh dipakai di produksi.
- DBHub lokal saat dokumen ini dibuat melaporkan MySQL 8.0.30. Lingkungan itu boleh dipakai untuk pengujian fungsional, tetapi tidak memenuhi bukti penerimaan MariaDB 10.11.

Query verifikasi melalui DBHub:

```sql
SELECT VERSION(), @@session.time_zone, @@character_set_database,
       @@collation_database, @@default_storage_engine;

SELECT table_name, engine, table_collation
FROM information_schema.tables
WHERE table_schema = 'medis' AND table_type = 'BASE TABLE';

SELECT trigger_name, event_manipulation, event_object_table
FROM information_schema.triggers
WHERE trigger_schema = 'medis'
ORDER BY event_object_table, trigger_name;
```

## Akun terpisah

Jangan gunakan `root` sebagai akun aplikasi. Simpan password di secret manager dan batasi host sesuai subnet deployment.

| Akun | Tujuan | Hak minimum |
| --- | --- | --- |
| `clinic_app` | Request web, worker, scheduler | DML pada `medis`; tanpa DDL, GRANT, atau akses schema lain |
| `clinic_migrator` | Pipeline deployment terkontrol | DDL, index, trigger, dan DML migrasi pada `medis` |
| `clinic_report` | Ekspor agregat/BI terjadwal | SELECT hanya pada view pelaporan yang disetujui |
| `clinic_backup` | Backup fisik/logis dan binlog | Hak backup minimum; tanpa hak aplikasi atau manajemen user |

Contoh awal yang harus disesuaikan dengan host dan kebijakan password organisasi:

```sql
CREATE USER 'clinic_app'@'10.%' IDENTIFIED BY '<secret-manager-value>';
GRANT SELECT, INSERT, UPDATE, DELETE ON medis.* TO 'clinic_app'@'10.%';

CREATE USER 'clinic_migrator'@'10.%' IDENTIFIED BY '<secret-manager-value>';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX,
      REFERENCES, CREATE VIEW, SHOW VIEW, TRIGGER
ON medis.* TO 'clinic_migrator'@'10.%';

CREATE USER 'clinic_report'@'10.%' IDENTIFIED BY '<secret-manager-value>';
GRANT SELECT ON medis_reporting.* TO 'clinic_report'@'10.%';

CREATE USER 'clinic_backup'@'localhost' IDENTIFIED BY '<secret-manager-value>';
GRANT SELECT, SHOW VIEW, TRIGGER, EVENT ON medis.* TO 'clinic_backup'@'localhost';
GRANT RELOAD, PROCESS, LOCK TABLES, REPLICATION CLIENT ON *.* TO 'clinic_backup'@'localhost';
```

Audit hak secara berkala dengan `SHOW GRANTS FOR ...` melalui DBHub. Cabut akun yang tidak lagi dipakai dan rotasi secret tanpa memasukkannya ke repository.

## Konfigurasi aplikasi produksi

Gunakan nilai berikut sebagai baseline, lalu isi host dan secret dari secret manager:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mariadb
DB_HOST=<private-database-host>
DB_PORT=3306
DB_DATABASE=medis
DB_USERNAME=clinic_app
DB_PASSWORD=<secret-manager-reference>

FILESYSTEM_DISK=private
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=<private-redis-host>
REDIS_PASSWORD=<secret-manager-reference>
```

`FILESYSTEM_DISK=private` mencegah dokumen rekam medis dilayani sebagai file publik. Redis harus berada di jaringan privat, memakai autentikasi, dan memiliki kebijakan persistence/monitoring yang sesuai RPO.

## Deployment database

1. Ambil backup terenkripsi dan pastikan backup terakhir sudah lolos verifikasi checksum.
2. Jalankan migrasi sebagai `clinic_migrator`: `php artisan migrate --force --no-interaction`.
3. Jalankan `php artisan migrate:status --no-interaction`.
4. Verifikasi engine, collation, session timezone, constraint, dan trigger melalui DBHub.
5. Jalankan smoke test aplikasi sebagai akun non-admin.
6. Simpan log deployment, hasil query verifikasi, dan checksum backup pada penyimpanan audit operasi.

Prosedur backup dan pemulihan berada di `docs/operations/backup-and-restore.md`.

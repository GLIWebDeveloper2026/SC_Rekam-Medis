# Backup dan Pemulihan MariaDB

Tujuan prosedur ini adalah menjaga data klinis, audit trail, dokumen, dan histori koreksi tetap utuh. Backup yang tidak pernah diuji restore belum dianggap valid.

## Kebijakan minimum

- Jalankan backup fisik MariaDB setiap hari dengan `mariadb-backup` pada host produksi.
- Jalankan backup logis terenkripsi setiap hari untuk portabilitas dan pemeriksaan independen.
- Arsipkan binary log untuk point-in-time recovery (PITR) dan lindungi dengan retensi yang konsisten dengan RPO.
- Simpan backup harian sekurang-kurangnya 35 hari, salinan bulanan sesuai kebijakan retensi klinik, dan satu salinan immutable/off-site.
- Enkripsi sebelum file meninggalkan host database. Helper menggunakan `age` dengan public recipient agar private key tidak berada pada host backup.
- Pakai akun `clinic_backup` melalui file option MariaDB yang ACL-nya hanya mengizinkan service account backup.
- Monitor exit code, umur backup terakhir, ukuran anomali, checksum, kapasitas storage, dan hasil restore drill.

## Persiapan kredensial

Buat file di luar repository, misalnya `C:\ProgramData\Clinic\secrets\backup.cnf`:

```ini
[client]
user=clinic_backup
password=<secret-manager-value>
ssl-mode=REQUIRED
```

Batasi ACL file tersebut untuk service account backup. Jangan menaruh password pada command line, environment bersama, log, atau Task Scheduler action.

## Backup logis terenkripsi

Pastikan `mariadb-dump` dari MariaDB 10.11 dan `age` tersedia pada `PATH`, lalu jalankan:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass `
  -File .\scripts\backup-mariadb.ps1 `
  -DefaultsExtraFile C:\ProgramData\Clinic\secrets\backup.cnf `
  -AgeRecipient age1exampleproductionrecipient `
  -BackupDirectory E:\ClinicBackups\logical `
  -RetentionDays 35
```

Helper menghasilkan file `.sql.age` dan checksum `.sha256`. Plaintext SQL hanya dibuat sebagai file sementara dan dihapus pada blok `finally`. Uji jadwal dengan `-WhatIf` sebelum mengaktifkan Task Scheduler.

Jadwalkan proses di luar jam sibuk, jalankan dengan service account khusus, dan kirim kegagalan ke kanal alert operasi. Salin artefak terenkripsi serta checksum ke storage off-site/immutable.

## Backup fisik dan binary log

Backup fisik memakai `mariadb-backup --backup`, kemudian `mariadb-backup --prepare` pada salinan terisolasi. Arsipkan binary log sejak koordinat backup penuh. Jangan menonaktifkan atau memangkas binlog sebelum salinan off-site dan restore drill berhasil.

Catat untuk setiap backup:

- waktu mulai/selesai dalam WIB dan UTC;
- versi MariaDB dan hostname sumber;
- posisi GTID/binlog awal dan akhir;
- checksum artefak;
- versi tool backup;
- hasil enkripsi, upload off-site, dan verifikasi.

## Restore drill terisolasi

Restore tidak boleh diarahkan ke database produksi. Siapkan host/instance terisolasi dengan versi MariaDB yang sama, lalu jalankan:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass `
  -File .\scripts\verify-restore.ps1 `
  -BackupFile E:\ClinicBackups\logical\medis-20260801-020000.sql.age `
  -DefaultsExtraFile C:\ProgramData\Clinic\secrets\migrator.cnf `
  -AgeIdentityFile C:\ProgramData\Clinic\secrets\backup-age-key.txt `
  -RestoreDatabase medis_restore_drill
```

Helper memeriksa checksum, mendekripsi ke direktori sementara, membuat database restore terisolasi, mengimpor dump, memeriksa jumlah tabel dan trigger, lalu menghapus database drill kecuali `-KeepDatabase` diberikan.

Setelah restore, lakukan juga pemeriksaan aplikasi:

1. Jalankan seluruh migration status dan smoke test pada koneksi restore.
2. Verifikasi hash chain audit, immutable trigger, jumlah akun/role, jumlah pasien/visit/encounter, dan checksum dokumen.
3. Pastikan audit trail tidak dipangkas, ditulis ulang, atau hilang.
4. Rekam waktu restore aktual, hasil pemeriksaan, penyimpangan, dan tindak lanjut.

Lakukan restore drill minimal setiap kuartal dan setelah perubahan besar pada schema, tool backup, enkripsi, atau infrastruktur.

## Point-in-time recovery

1. Pulihkan backup penuh terakhir ke instance terisolasi.
2. Temukan posisi GTID/binlog yang tercatat pada backup.
3. Gunakan `mariadb-binlog` untuk memutar event setelah backup sampai tepat sebelum waktu/GTID insiden.
4. Verifikasi integritas data dan audit trail pada instance terisolasi.
5. Setelah persetujuan incident commander dan pemilik data, lakukan cutover terkontrol; jangan menimpa produksi tanpa rollback plan.

PITR harus diuji sebagai bagian restore drill. Simpan perintah, rentang binlog, persetujuan, dan hasil verifikasi sebagai bukti operasi.

## Respons kegagalan

- Backup gagal: pertahankan backup valid terakhir, alert segera, perbaiki akar masalah, lalu ulangi backup.
- Checksum gagal: karantina artefak dan jangan gunakan untuk restore.
- Restore drill gagal: tandai backup tidak tervalidasi, eskalasi, dan ulangi dari artefak sebelumnya.
- Kehilangan key enkripsi: perlakukan sebagai kehilangan backup; key harus memiliki escrow dan prosedur pemulihan terpisah.

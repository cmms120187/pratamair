# Fix Migration Error di Production

## Masalah
Migration `2025_12_11_095930_add_work_hours_to_production_daily_grades_table` gagal karena kolom `target_per_hour` sudah ada.

## Solusi

### Opsi 1: Via SQL (Paling Mudah - Recommended)
Jalankan query SQL ini di phpMyAdmin atau database client Hostinger:

```sql
-- Hapus record migration yang gagal
DELETE FROM migrations 
WHERE migration = '2025_12_11_095930_add_work_hours_to_production_daily_grades_table';
```

Lalu jalankan:
```bash
php artisan migrate
```

### Opsi 1b: Via PHP Script
Jika tinker tidak bisa digunakan (shell_exec disabled):

```bash
# Upload file fix_migration.php ke root project
# Jalankan via browser: https://yourdomain.com/fix_migration.php
# ATAU via CLI: php fix_migration.php

# Setelah selesai, HAPUS file fix_migration.php untuk keamanan!
```

### Opsi 2: Hapus Record Migration yang Gagal, Lalu Jalankan Lagi
Migration sudah dimodifikasi untuk mengecek kolom sebelum menambahkannya. Setelah git pull:

```bash
# Hapus record migration yang gagal dari tabel migrations
php artisan tinker
>>> DB::table('migrations')->where('migration', '2025_12_11_095930_add_work_hours_to_production_daily_grades_table')->delete();
>>> exit

# Jalankan migration lagi (migration sudah aman sekarang - akan skip kolom yang sudah ada)
php artisan migrate
```

### Opsi 3: Tambahkan Kolom yang Belum Ada Manual
Jika ada kolom yang belum ada:

```sql
-- Cek kolom yang ada
DESCRIBE production_daily_grades;

-- Tambahkan kolom yang belum ada (contoh)
ALTER TABLE production_daily_grades 
ADD COLUMN IF NOT EXISTS target_per_hour INT NULL AFTER production_date,
ADD COLUMN IF NOT EXISTS start_time TIME NULL AFTER target_per_hour,
ADD COLUMN IF NOT EXISTS end_time TIME NULL AFTER start_time,
ADD COLUMN IF NOT EXISTS break_duration DECIMAL(3,1) NULL AFTER end_time;
```

Lalu mark migration sebagai sudah dijalankan (Opsi 1).

## Setelah Fix Migration

Pastikan migration baru berjalan:
```bash
php artisan migrate
```

Migration yang harus berjalan:
- `2025_12_12_084858_create_production_daily_downtimes_table` ✅


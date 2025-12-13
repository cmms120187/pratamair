# Fix Duplicate Migration di Production

## Masalah
Ada 2 migration file dengan nama mirip:
1. `2025_12_11_095930_add_work_hours_to_production_daily_grades_table` ✅ (sudah DONE)
2. `2025_12_11_095930_add_work_hours_to_production_daily_grades_tables` ❌ (masih FAIL - dengan 's' di akhir)

## Solusi

### Langkah 1: Hapus File Duplikat di Production
Via SSH atau File Manager di Hostinger, cek apakah ada file:
```
database/migrations/2025_12_11_095930_add_work_hours_to_production_daily_grades_tables.php
```

Jika ada, **HAPUS file tersebut** karena ini adalah duplikat.

### Langkah 2: Hapus Record dari Tabel Migrations
Jalankan SQL ini di phpMyAdmin:

```sql
-- Hapus record migration duplikat (yang dengan 's' di akhir)
DELETE FROM migrations 
WHERE migration = '2025_12_11_095930_add_work_hours_to_production_daily_grades_tables';
```

### Langkah 3: Pastikan File yang Benar Ada
Pastikan hanya ada 1 file migration:
```
database/migrations/2025_12_11_095930_add_work_hours_to_production_daily_grades_table.php
```
(Tanpa 's' di akhir 'table')

### Langkah 4: Jalankan Migration Lagi
```bash
php artisan migrate
```

## Verifikasi

Cek apakah masih ada duplikat:
```bash
# Via SSH
ls -la database/migrations/*add_work_hours*

# Seharusnya hanya ada 1 file:
# 2025_12_11_095930_add_work_hours_to_production_daily_grades_table.php
```

## Catatan
File yang benar adalah yang **tanpa 's'** di akhir (`table` bukan `tables`).



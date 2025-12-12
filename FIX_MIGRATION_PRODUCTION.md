# Fix Migration Error di Production

## Masalah
Migration `2025_12_11_095930_add_work_hours_to_production_daily_grades_table` gagal karena kolom `target_per_hour` sudah ada.

## Solusi

### Opsi 1: Mark Migration sebagai Sudah Dijalankan (Jika kolom sudah ada semua)
Jika semua kolom (`target_per_hour`, `start_time`, `end_time`, `break_duration`) sudah ada di tabel:

```bash
# Cek apakah kolom sudah ada
php artisan tinker
>>> Schema::hasColumn('production_daily_grades', 'target_per_hour')
>>> Schema::hasColumn('production_daily_grades', 'start_time')
>>> Schema::hasColumn('production_daily_grades', 'end_time')
>>> Schema::hasColumn('production_daily_grades', 'break_duration')
>>> exit

# Jika semua kolom sudah ada, insert record ke migrations table
php artisan tinker
>>> DB::table('migrations')->insert([
    'migration' => '2025_12_11_095930_add_work_hours_to_production_daily_grades_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
>>> exit

# Lalu lanjutkan migration lainnya
php artisan migrate
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


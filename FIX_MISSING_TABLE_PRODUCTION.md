# Fix Missing Table: production_daily_downtimes

## Masalah
Error di production: `Table 'production_daily_downtimes' doesn't exist`

## Penyebab
Migration untuk table `production_daily_downtimes` belum dijalankan di database production.

## Solusi

### Di Hostinger (Production Server)

#### 1. Cek Status Migration
```bash
php artisan migrate:status
```

Ini akan menampilkan daftar migration yang sudah dan belum dijalankan.

#### 2. Jalankan Migration yang Belum Dijalankan
```bash
# Jalankan semua migration yang belum dijalankan
php artisan migrate --force
```

**PENTING:** Flag `--force` diperlukan untuk production environment.

#### 3. Verifikasi Table Sudah Dibuat
```bash
# Cek apakah table sudah ada
php artisan tinker
# Di dalam tinker:
Schema::hasTable('production_daily_downtimes')
# Harus return: true
exit
```

#### 4. Jika Ada Error Foreign Key
Jika ada error foreign key constraint, pastikan table `production_daily_grades` sudah ada terlebih dahulu:

```bash
# Cek table production_daily_grades
php artisan tinker
Schema::hasTable('production_daily_grades')
exit
```

Jika `production_daily_grades` belum ada, jalankan migration untuk table tersebut juga.

## Migration File yang Perlu Dijalankan

File migration yang perlu dijalankan:
- `2025_12_12_084858_create_production_daily_downtimes_table.php`

Dependencies (harus sudah ada):
- `2025_12_04_034028_create_production_daily_grades_table.php` (harus dijalankan terlebih dahulu)

## Checklist

- [ ] Cek status migration: `php artisan migrate:status`
- [ ] Pastikan migration file sudah ter-pull dari git
- [ ] Jalankan migration: `php artisan migrate --force`
- [ ] Verifikasi table sudah dibuat
- [ ] Test halaman OEE: `tpmir2.tpmcmms.id/oee`

## Troubleshooting

### Error: "Foreign key constraint fails"
**Solusi:**
1. Pastikan table `production_daily_grades` sudah ada
2. Jika belum, jalankan migration untuk `production_daily_grades` terlebih dahulu
3. Kemudian jalankan migration untuk `production_daily_downtimes`

### Error: "Table already exists"
**Solusi:**
1. Cek apakah table benar-benar ada: `php artisan tinker` lalu `Schema::hasTable('production_daily_downtimes')`
2. Jika table sudah ada, mungkin ada masalah dengan migration status
3. Reset migration status: `php artisan migrate:status` untuk melihat status

### Error: "Migration table not found"
**Solusi:**
1. Buat migration table: `php artisan migrate:install`
2. Kemudian jalankan: `php artisan migrate --force`

## Perintah Lengkap untuk Deploy

```bash
# 1. Pull dari git (jika belum)
git pull origin main

# 2. Regenerate autoload
composer dump-autoload --optimize --no-dev --ignore-platform-reqs

# 3. Jalankan migration
php artisan migrate --force

# 4. Clear cache
php artisan optimize:clear

# 5. Cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```


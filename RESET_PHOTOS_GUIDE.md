# Panduan Reset Semua Foto ke Database

## Tujuan
Mereset semua link foto ke database `photos` sehingga semua foto baru yang di-upload akan masuk ke database.

## Yang Akan Dilakukan

### 1. Reset photo_id di Semua Tabel
- `standards.photo_id` → NULL
- `machine_erp.photo_id` → NULL
- `machine_types.photo_id` → NULL
- `models.photo_id` → NULL
- `users.photo_id` → NULL
- `maintenance_points.photo_id` → NULL

### 2. Hapus Semua Data dari Tabel photos
- Semua record di tabel `photos` akan dihapus
- Auto increment akan direset ke 1

### 3. Bersihkan Pivot Table
- `standard_standard_photo` akan dikosongkan

### 4. File Foto di Storage
⚠️ **TIDAK AKAN DIHAPUS** - File foto tetap ada di `storage/app/public/` untuk keamanan

## Cara Menjalankan

### Step 1: Backup Database (PENTING!)
```bash
# Backup database dulu sebelum reset
mysqldump -u username -p database_name > backup_before_reset.sql
```

### Step 2: Jalankan Seeder Reset
```bash
php artisan db:seed --class=ResetAllPhotosSeeder
```

Atau jika ingin non-interactive:
```bash
php artisan db:seed --class=ResetAllPhotosSeeder --force
```

### Step 3: Verifikasi Reset
```sql
-- Cek apakah photo_id sudah NULL semua
SELECT COUNT(*) FROM standards WHERE photo_id IS NOT NULL;
SELECT COUNT(*) FROM machine_erp WHERE photo_id IS NOT NULL;
SELECT COUNT(*) FROM machine_types WHERE photo_id IS NOT NULL;

-- Cek apakah tabel photos kosong
SELECT COUNT(*) FROM photos;
```

### Step 4: Upload Ulang Foto
Setelah reset, semua foto baru yang di-upload akan otomatis masuk ke tabel `photos` karena controller sudah diupdate.

## Opsi: Hapus File Foto Juga (Opsional)

Jika ingin menghapus file foto dari storage juga, jalankan perintah berikut:

```bash
# Hapus semua file foto dari storage (HATI-HATI!)
# Pastikan sudah backup dulu!

# Hapus folder standards
php artisan tinker
Storage::disk('public')->deleteDirectory('standards');

# Hapus folder machine-types
Storage::disk('public')->deleteDirectory('machine-types');

# Hapus folder machine-erp
Storage::disk('public')->deleteDirectory('machine-erp');

# Hapus folder maintenance-points
Storage::disk('public')->deleteDirectory('maintenance-points');

# Hapus folder users
Storage::disk('public')->deleteDirectory('users');

# Hapus folder activities
Storage::disk('public')->deleteDirectory('activities');
```

**ATAU** buat script terpisah untuk menghapus semua file foto.

## Setelah Reset

### 1. Pastikan Controller Sudah Update
Pastikan semua controller sudah menggunakan `ImageHelper::saveToDatabase()` untuk upload foto baru.

### 2. Upload Foto Baru
- Upload foto melalui form di aplikasi
- Foto akan otomatis masuk ke tabel `photos`
- `photo_id` akan otomatis terisi

### 3. Verifikasi Upload
```sql
-- Cek foto baru yang sudah diupload
SELECT * FROM photos ORDER BY created_at DESC LIMIT 10;

-- Cek apakah photo_id sudah terisi
SELECT COUNT(*) FROM standards WHERE photo_id IS NOT NULL;
```

## Catatan Penting

1. ⚠️ **Backup Database Dulu** - Reset tidak bisa di-undo
2. ⚠️ **File Foto Tidak Dihapus** - File tetap ada di storage untuk keamanan
3. ✅ **Upload Baru Akan Masuk Database** - Semua upload baru akan otomatis masuk ke `photos`
4. ✅ **Controller Sudah Siap** - Controller sudah diupdate untuk menggunakan Photo model

## Troubleshooting

### Error: Foreign Key Constraint
Jika ada error foreign key constraint, disable dulu:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Jalankan reset
SET FOREIGN_KEY_CHECKS = 1;
```

### Error: Table Not Found
Pastikan semua migration sudah dijalankan:
```bash
php artisan migrate
```

---

**Dibuat**: {{ date('Y-m-d H:i:s') }}  
**Status**: ✅ Siap Digunakan

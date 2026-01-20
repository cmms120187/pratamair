# Status Foto di Database - Audit Lengkap

## Tanggal Audit: {{ date('Y-m-d H:i:s') }}

## 1. STRUKTUR DATABASE FOTO

### 1.1 Tabel `photos` (Sistem Baru - Centralized)
✅ **Tabel sudah dibuat** dengan migration `2026_01_09_033707_create_photos_table.php`

**Struktur:**
- `id` - Primary key
- `original_filename` - Nama file asli saat upload
- `stored_filename` - Nama file yang disimpan
- `file_path` - Path relatif di storage
- `file_size` - Ukuran file
- `mime_type` - Tipe MIME (image/jpeg, image/png, image/webp)
- `width` - Lebar gambar (pixel)
- `height` - Tinggi gambar (pixel)
- `related_type` - Tipe relasi (standard, machine_erp, machine_type, user, dll)
- `related_id` - ID dari tabel terkait
- `description` - Deskripsi foto
- `uploaded_by` - User yang upload
- `timestamps`

### 1.2 Tabel `standard_photos` (Sistem Lama - Legacy)
⚠️ **Masih ada** - Tabel ini adalah sistem lama untuk Standards yang menggunakan many-to-many relationship

**Struktur:**
- `id` - Primary key
- `standard_id` - Foreign key ke standards (nullable)
- `photo_path` - Path ke file photo
- `name` - Nama photo
- `timestamps`

### 1.3 Kolom `photo_id` di Tabel Lain
✅ **Sudah ditambahkan** ke beberapa tabel:
- ✅ `standards` - Migration: `2026_01_09_034254_add_photo_id_to_standards_table.php`
- ✅ `machine_erp` - Migration: `2026_01_09_034455_add_photo_id_to_machine_erp_table.php`
- ✅ `machine_types` - Migration: `2026_01_09_034438_add_photo_id_to_machine_types_table.php`
- ✅ `models` - Migration: `2026_01_09_034508_add_photo_id_to_models_table.php`
- ✅ `users` - Migration: `2026_01_09_034502_add_photo_id_to_users_table.php`
- ✅ `maintenance_points` - Migration: `2026_01_09_034449_add_photo_id_to_maintenance_points_table.php`

### 1.4 Kolom `photo` (Legacy Path)
⚠️ **Masih ada** di beberapa tabel:
- `standards.photo` - Path langsung (legacy)
- `machine_types.photo` - Path langsung (legacy)
- `models.photo` - Path langsung (legacy)
- `users.photo` - Path langsung (legacy)
- `machine_erp.photo` - Path langsung (legacy)
- `maintenance_points.photo` - Path langsung (legacy)
- `activities.photos` - JSON array of paths (legacy)

## 2. STATUS MIGRASI FOTO

### 2.1 Seeder untuk Migrasi
✅ **Seeder sudah tersedia**: `MigrateAllPhotosToDatabaseSeeder`

Seeder ini akan:
1. Migrate photos dari `public/images` (kecuali logo_tpm.png)
2. Migrate photos dari `storage/app/public/standards`
3. Migrate photos dari `storage/app/public/machine-types`
4. Migrate photos dari `storage/app/public/maintenance-points`
5. Migrate photos dari `storage/app/public/users`
6. Migrate photos dari `storage/app/public/activities`
7. Migrate photos dari `storage/app/public/machine-erp`
8. Migrate `StandardPhoto` ke `Photo`
9. Migrate `Standard.photo` field ke `Photo`

### 2.2 Apakah Seeder Sudah Dijalankan?
❓ **TIDAK DIKETAHUI** - Perlu dicek di database production

**Cara cek:**
```sql
-- Cek apakah ada data di tabel photos
SELECT COUNT(*) as total_photos FROM photos;

-- Cek apakah ada standard dengan photo_id
SELECT COUNT(*) as standards_with_photo_id FROM standards WHERE photo_id IS NOT NULL;

-- Cek apakah masih ada standard dengan photo (legacy)
SELECT COUNT(*) as standards_with_photo FROM standards WHERE photo IS NOT NULL;
```

## 3. SISTEM YANG DIGUNAKAN SAAT INI

### 3.1 Standards
**Status**: ⚠️ **Hybrid System** (menggunakan 3 sistem sekaligus)
1. **Priority 1**: `photo_id` → `photos` table (sistem baru) ✅
2. **Priority 2**: `photos` relationship → `standard_photos` table (many-to-many) ⚠️
3. **Priority 3**: `photo` field → Path langsung (legacy) ⚠️

**Kode di `Standard.php`:**
```php
// Priority 1: photo_id (new system)
if ($this->photo_id && $this->photoModel) {
    return route('photos.show', $this->photo_id);
}

// Priority 2: photos relationship (many-to-many)
if ($this->photos && $this->photos->count() > 0) {
    // Try to find in photos table
    // Fallback to old path
}

// Priority 3: photo field (legacy)
if ($this->photo) {
    // Try to find in photos table
    // Fallback to old path
}
```

### 3.2 Machine ERP
**Status**: ⚠️ **Belum sepenuhnya menggunakan photos table**

**Kemungkinan masih menggunakan:**
- `photo` field (path langsung)
- `photo_id` (jika sudah dimigrasi)

### 3.3 Machine Types
**Status**: ⚠️ **Belum sepenuhnya menggunakan photos table**

**Kemungkinan masih menggunakan:**
- `photo` field (path langsung)
- `photo_id` (jika sudah dimigrasi)

### 3.4 Models
**Status**: ⚠️ **Belum sepenuhnya menggunakan photos table**

**Kemungkinan masih menggunakan:**
- `photo` field (path langsung)
- `photo_id` (jika sudah dimigrasi)

### 3.5 Users
**Status**: ⚠️ **Belum sepenuhnya menggunakan photos table**

**Kemungkinan masih menggunakan:**
- `photo` field (path langsung)
- `photo_id` (jika sudah dimigrasi)

### 3.6 Activities
**Status**: ⚠️ **Masih menggunakan JSON array of paths**

**Kemungkinan masih menggunakan:**
- `photos` field (JSON array)

## 4. REKOMENDASI

### 4.1 Prioritas Tinggi
1. **Jalankan Seeder Migrasi**:
   ```bash
   php artisan db:seed --class=MigrateAllPhotosToDatabaseSeeder
   ```
   
   **PENTING**: Backup database dulu sebelum menjalankan seeder!

2. **Verifikasi Migrasi**:
   - Cek apakah semua foto sudah masuk ke tabel `photos`
   - Cek apakah `photo_id` sudah terisi di tabel terkait
   - Cek apakah foto masih bisa diakses

### 4.2 Prioritas Menengah
1. **Update Controller untuk Menggunakan Photo Model**:
   - Pastikan semua controller yang upload foto menggunakan `ImageHelper::savePhotoToDatabase()`
   - Pastikan semua controller yang menampilkan foto menggunakan `photo_id` atau `Photo` model

2. **Update View untuk Menggunakan Photo Model**:
   - Pastikan semua view menggunakan `route('photos.show', $photo_id)` atau `$model->photoModel->url`

### 4.3 Prioritas Rendah
1. **Hapus Kolom Legacy** (setelah semua foto dimigrasi):
   - Hapus kolom `photo` dari tabel yang sudah punya `photo_id`
   - Hapus tabel `standard_photos` jika sudah tidak digunakan
   - Hapus kolom `photos` (JSON) dari `activities` jika sudah tidak digunakan

## 5. CHECKLIST MIGRASI

### Sebelum Migrasi
- [ ] Backup database
- [ ] Backup folder `storage/app/public`
- [ ] Backup folder `public/images`
- [ ] Cek apakah semua file foto masih ada

### Saat Migrasi
- [ ] Jalankan seeder: `php artisan db:seed --class=MigrateAllPhotosToDatabaseSeeder`
- [ ] Cek log untuk error
- [ ] Verifikasi jumlah foto yang dimigrasi

### Setelah Migrasi
- [ ] Cek apakah semua foto masih bisa diakses
- [ ] Cek apakah `photo_id` sudah terisi
- [ ] Test upload foto baru
- [ ] Test hapus foto
- [ ] Test edit foto

## 6. CARA CEK STATUS DI DATABASE

### Query untuk Cek Status:
```sql
-- 1. Total foto di tabel photos
SELECT COUNT(*) as total_photos FROM photos;

-- 2. Foto per related_type
SELECT related_type, COUNT(*) as count 
FROM photos 
GROUP BY related_type;

-- 3. Standards dengan photo_id
SELECT COUNT(*) as with_photo_id 
FROM standards 
WHERE photo_id IS NOT NULL;

-- 4. Standards dengan photo (legacy)
SELECT COUNT(*) as with_photo 
FROM standards 
WHERE photo IS NOT NULL;

-- 5. Standards dengan photos relationship
SELECT COUNT(*) as with_photos_relation
FROM standards s
INNER JOIN standard_standard_photo ssp ON s.id = ssp.standard_id;

-- 6. Machine ERP dengan photo_id
SELECT COUNT(*) as with_photo_id 
FROM machine_erp 
WHERE photo_id IS NOT NULL;

-- 7. Machine Types dengan photo_id
SELECT COUNT(*) as with_photo_id 
FROM machine_types 
WHERE photo_id IS NOT NULL;
```

## 7. KESIMPULAN

### Status Saat Ini: ⚠️ **BELUM SEMUA FOTO MASUK DATABASE**

**Yang Sudah:**
- ✅ Tabel `photos` sudah dibuat
- ✅ Kolom `photo_id` sudah ditambahkan ke beberapa tabel
- ✅ Seeder migrasi sudah tersedia
- ✅ Helper `ImageHelper::savePhotoToDatabase()` sudah ada
- ✅ Controller `PhotoController` sudah ada

**Yang Belum:**
- ❓ Seeder migrasi belum tentu sudah dijalankan
- ⚠️ Masih ada sistem legacy (kolom `photo`, `standard_photos`)
- ⚠️ Beberapa controller mungkin masih menggunakan path langsung
- ⚠️ Beberapa view mungkin masih menggunakan path langsung

### Langkah Selanjutnya:
1. **Cek database production** - Apakah seeder sudah dijalankan?
2. **Jalankan seeder** jika belum
3. **Verifikasi** semua foto bisa diakses
4. **Update controller/view** untuk menggunakan Photo model secara konsisten

---

**Dibuat**: {{ date('Y-m-d H:i:s') }}  
**Status**: ⚠️ Perlu Verifikasi dan Migrasi


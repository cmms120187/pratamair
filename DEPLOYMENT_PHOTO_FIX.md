# Fix Foto Standards Tidak Muncul di Hostinger

## Masalah
Foto di halaman Standards tidak terlihat saat di-deploy ke Hostinger, padahal di lokal berfungsi dengan baik.

## Penyebab
1. **Storage Link Belum Dibuat**: Symbolic link dari `public/public-storage` ke `storage/app/public` belum dibuat di server Hostinger
2. **Path Tidak Konsisten**: Beberapa view menggunakan `asset('public-storage/...')` yang bergantung pada symbolic link

## Solusi

### 1. Pastikan Storage Link Sudah Dibuat
Jalankan perintah berikut di server Hostinger melalui SSH atau terminal:

```bash
php artisan storage:link
```

Perintah ini akan membuat symbolic link dari `public/public-storage` ke `storage/app/public`.

### 2. Verifikasi Storage Link
Setelah menjalankan `php artisan storage:link`, pastikan link sudah dibuat:

```bash
ls -la public/public-storage
```

Harus menunjukkan bahwa `public/public-storage` adalah symbolic link ke `../storage/app/public`.

### 3. Periksa Permissions
Pastikan folder storage memiliki permission yang benar:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. Periksa Konfigurasi Filesystem
Pastikan file `config/filesystems.php` memiliki konfigurasi yang benar:

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/public-storage',
    'visibility' => 'public',
],
```

### 5. Perubahan yang Sudah Dilakukan
- ✅ Mengubah `resources/views/standards/index.blade.php` untuk menggunakan `Storage::disk('public')->url()` instead of `asset('public-storage/...')`
- ✅ Mengubah `app/Models/Standard.php` method `getPhotoUrlAttribute()` untuk menggunakan `Storage::disk('public')->url()`

## Testing

### Di Local (Sebelum Deploy)
1. Pastikan foto bisa dilihat di halaman Standards
2. Cek console browser untuk error 404 pada gambar

### Di Hostinger (Setelah Deploy)
1. SSH ke server Hostinger
2. Jalankan `php artisan storage:link`
3. Refresh halaman Standards
4. Cek console browser untuk error 404 pada gambar
5. Jika masih error, cek:
   - Apakah file foto ada di `storage/app/public/standards/`
   - Apakah symbolic link sudah dibuat
   - Apakah permission folder storage sudah benar

## Troubleshooting

### Foto Masih Tidak Muncul
1. **Cek apakah file ada di storage**:
   ```bash
   ls -la storage/app/public/standards/
   ```

2. **Cek apakah symbolic link ada**:
   ```bash
   ls -la public/public-storage
   ```

3. **Cek permission**:
   ```bash
   ls -la storage/app/public/
   ```

4. **Clear cache**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

### Error 403 Forbidden
Jika mendapatkan error 403, kemungkinan permission folder salah:
```bash
chmod -R 775 storage/app/public
chown -R www-data:www-data storage/app/public  # atau user yang sesuai
```

### Error 404 Not Found
Jika mendapatkan error 404:
1. Pastikan symbolic link sudah dibuat
2. Pastikan file ada di `storage/app/public/standards/`
3. Cek `.htaccess` di folder `public` tidak memblokir akses ke `public-storage`

## Catatan Penting

1. **Jangan commit folder `public/public-storage`** ke git karena ini adalah symbolic link
2. **Pastikan folder `storage/app/public` ada** dan memiliki permission yang benar
3. **Setelah deploy, selalu jalankan `php artisan storage:link`** jika belum pernah dijalankan
4. **Gunakan `Storage::disk('public')->url()`** untuk mendapatkan URL yang benar, bukan `asset('public-storage/...')`

## Checklist Deployment

- [ ] Jalankan `php artisan storage:link` di server
- [ ] Verifikasi symbolic link sudah dibuat
- [ ] Cek permission folder storage
- [ ] Clear cache: `php artisan config:clear && php artisan cache:clear`
- [ ] Test akses foto di halaman Standards
- [ ] Cek console browser untuk error

---

**Dibuat**: {{ date('Y-m-d H:i:s') }}  
**Status**: ✅ Fix Applied


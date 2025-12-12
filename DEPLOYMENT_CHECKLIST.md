# Deployment Checklist untuk Hostinger

## Masalah yang Ditemukan
Navbar dengan submenu tidak bisa diakses di production karena Alpine.js tidak ter-load dengan benar.

## Solusi yang Sudah Diterapkan

### 1. Alpine.js CDN Fallback
- Menambahkan fallback untuk load Alpine.js dari CDN jika tidak ter-load dari Vite
- File: `resources/views/layouts/app.blade.php`

### 2. Vanilla JavaScript Fallback
- Menambahkan fallback JavaScript murni untuk toggle submenu jika Alpine.js tidak berfungsi
- File: `resources/views/layouts/app.blade.php` (di bagian bawah)

### 3. Class Tambahan untuk Menu
- Menambahkan class `menu-group-item`, `menu-group-toggle`, `menu-submenu`, `menu-arrow` untuk memudahkan seleksi dengan vanilla JS
- File: `resources/views/layouts/navigation.blade.php`

## Langkah Deployment ke Hostinger

### 1. Build Assets untuk Production
```bash
npm run build
```
Ini akan membuat file di folder `public/build/` yang perlu di-upload ke server.

### 2. Pastikan File yang Di-upload
- ✅ Folder `public/build/` (dari hasil `npm run build`)
- ✅ Semua file PHP
- ✅ Folder `config/` (termasuk `config/menu.php`)
- ✅ Folder `resources/views/`
- ✅ Folder `routes/`
- ✅ Folder `app/`
- ✅ File `.env` (dengan konfigurasi production)

### 3. Set Environment Variables
Pastikan di `.env` production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Pastikan Vite assets ter-load
VITE_APP_NAME="${APP_NAME}"
```

### 4. Clear Cache di Production
Setelah upload, jalankan di server:
```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
php artisan optimize
```

### 5. Set Permission
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Test di Browser
1. Buka browser console (F12)
2. Cek apakah ada error JavaScript
3. Cek apakah Alpine.js ter-load: ketik `Alpine` di console, harus return object
4. Test klik menu dengan submenu (Location, Machinary, dll)

## Troubleshooting

### Jika Submenu Masih Tidak Bisa Diklik:

1. **Cek Browser Console**
   - Buka Developer Tools (F12)
   - Cek tab Console untuk error JavaScript
   - Cek tab Network untuk file yang gagal load

2. **Cek Apakah Alpine.js Ter-load**
   ```javascript
   // Di browser console, ketik:
   typeof Alpine
   // Harus return "object" atau "function"
   ```

3. **Cek Apakah Assets Vite Ter-load**
   - Buka tab Network di Developer Tools
   - Reload halaman
   - Cek apakah file dari `/build/assets/` ter-load dengan status 200

4. **Cek Fallback JavaScript**
   - Jika Alpine.js tidak ter-load, fallback JavaScript akan aktif
   - Cek console untuk pesan: "Alpine.js not detected, using fallback menu functionality"

5. **Manual Test Fallback**
   - Jika Alpine.js tidak berfungsi, fallback akan menggunakan class `.menu-group-toggle`
   - Cek apakah class tersebut ada di HTML menu dengan submenu

### Jika Masih Bermasalah:

1. **Pastikan Vite Build Berhasil**
   ```bash
   npm run build
   # Harus tidak ada error
   ```

2. **Pastikan File Build Ter-upload**
   - Cek apakah folder `public/build/` ada di server
   - Cek apakah file `manifest.json` ada di `public/build/`

3. **Cek Path Assets**
   - Pastikan `APP_URL` di `.env` benar
   - Pastikan `asset()` helper mengarah ke path yang benar

4. **Cek CSP (Content Security Policy)**
   - Jika server menggunakan CSP, pastikan Alpine.js dan inline script diizinkan

## Catatan Penting

- **Jangan lupa build assets** sebelum deploy: `npm run build`
- **Upload folder `public/build/`** ke server
- **Clear semua cache** setelah deploy
- **Test di browser** dengan console terbuka untuk melihat error

## File yang Diubah untuk Fix

1. `resources/views/layouts/app.blade.php` - Menambahkan Alpine.js CDN fallback dan vanilla JS fallback
2. `resources/views/layouts/navigation.blade.php` - Menambahkan class untuk fallback JS


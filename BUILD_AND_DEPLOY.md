# Panduan Build dan Deploy Assets ke Hostinger

## Masalah
CSS dan JavaScript terblokir karena aplikasi masih mencoba load dari Vite dev server (`http://[::1]:5173`) padahal sudah di production.

## Solusi

### 1. Build Assets untuk Production

Jalankan di komputer lokal (sebelum upload):

```bash
npm run build
```

Ini akan membuat folder `public/build/` yang berisi:
- `manifest.json` - File manifest Vite
- `assets/` - Folder berisi CSS dan JS yang sudah di-compile

### 2. Upload Folder `public/build/` ke Server

**PENTING:** Upload seluruh folder `public/build/` ke server Hostinger:
- Folder: `public/build/`
- File: `public/build/manifest.json`
- Folder: `public/build/assets/` (berisi semua file CSS dan JS)

### 3. Pastikan File `.env` di Production Benar

Di server Hostinger, pastikan file `.env` memiliki:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tpmir2.tpmcmms.id
```

### 4. Hapus File `public/hot` (Jika Ada)

Jika ada file `public/hot` di server, **HAPUS** file tersebut. File ini menandakan Vite masih dalam mode development.

### 5. Clear Cache di Server

Setelah upload, jalankan di server:

```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
php artisan optimize
```

### 6. Set Permission (Jika Perlu)

```bash
chmod -R 755 public/build
chmod -R 775 storage bootstrap/cache
```

## Checklist Deployment

- [ ] Run `npm run build` di lokal
- [ ] Cek folder `public/build/` ada dan berisi file
- [ ] Upload folder `public/build/` ke server
- [ ] Pastikan `public/build/manifest.json` ada di server
- [ ] Pastikan `public/build/assets/` ada dan berisi file CSS/JS
- [ ] Hapus file `public/hot` jika ada di server
- [ ] Set `.env` dengan `APP_ENV=production`
- [ ] Clear semua cache
- [ ] Test di browser dengan hard refresh (Ctrl+F5)

## Verifikasi

Setelah deploy, cek di browser:
1. Buka Developer Tools (F12)
2. Tab Network
3. Reload halaman
4. Cek apakah file dari `/build/assets/` ter-load dengan status 200
5. Tidak ada error CORS di console

## Troubleshooting

### Jika masih error CORS:
1. Pastikan folder `public/build/` sudah ter-upload
2. Pastikan file `public/build/manifest.json` ada
3. Hapus file `public/hot` jika ada
4. Clear cache: `php artisan config:clear && php artisan view:clear`
5. Hard refresh browser (Ctrl+F5)

### Jika CSS masih tidak ter-load:
1. Cek path di browser Network tab
2. Pastikan file CSS ada di `public/build/assets/`
3. Cek permission folder `public/build/`


# Deployment Checklist untuk Hostinger

## Setelah Git Pull di Hostinger

### 1. Hapus File `public/hot` (PENTING!)
File ini menyebabkan aplikasi mencoba load assets dari dev server Vite.

```bash
# Via SSH atau File Manager di Hostinger
rm public/hot
# atau
rm -f public/hot
```

### 2. Build Assets untuk Production (PENTING!)
**Ini HARUS dilakukan setelah git pull!**

```bash
# Pastikan Node.js dan npm terinstall di Hostinger
node --version
npm --version

# Install dependencies (jika belum)
npm install

# Build assets untuk production
npm run build
```

Ini akan membuat file di folder `public/build/` yang digunakan di production:
- `public/build/manifest.json`
- `public/build/assets/app-*.css`
- `public/build/assets/app-*.js`

**Catatan:** File di folder `public/build/` harus di-commit dan di-upload ke production, ATAU build langsung di production server.

### 3. Jalankan Migration
```bash
php artisan migrate
```

Pastikan migration baru sudah dijalankan:
- `2025_12_12_084858_create_production_daily_downtimes_table.php`

### 4. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 5. Optimize untuk Production (Opsional)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Set Permission (jika perlu)
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Troubleshooting

### Error: "Access to script at 'http://[::1]:5173/@vite/client' has been blocked by CORS policy"
**Solusi:** Hapus file `public/hot` dan pastikan `npm run build` sudah dijalankan.

### Error: "GET https://tpmir2.tpmcmms.id/build/assets/app-*.css net::ERR_ABORTED 404 (Not Found)"
**Solusi:** 
1. Pastikan `npm run build` sudah dijalankan di production
2. Cek apakah folder `public/build/assets/` ada dan berisi file CSS/JS
3. Pastikan permission folder `public/build` adalah 755
4. Clear cache: `php artisan view:clear`

### Error: "Table 'production_daily_downtimes' doesn't exist"
**Solusi:** Jalankan `php artisan migrate`

### Error: "Class 'App\Models\ProductionDailyDowntime' not found"
**Solusi:** 
1. Pastikan file model sudah ter-commit dan ter-pull
2. Clear cache: `php artisan config:clear`

### Form Edit tidak menampilkan section Downtime
**Solusi:**
1. Pastikan file `resources/views/production_daily/edit.blade.php` sudah ter-update
2. Pastikan file `resources/views/production_daily/partials/downtime-entry.blade.php` sudah ada
3. Clear view cache: `php artisan view:clear`

## Checklist Cepat

- [ ] Hapus `public/hot`
- [ ] `npm install && npm run build`
- [ ] `php artisan migrate`
- [ ] `php artisan config:clear`
- [ ] `php artisan cache:clear`
- [ ] `php artisan view:clear`
- [ ] Test halaman OEE
- [ ] Test form Production Daily (create & edit)
- [ ] Test input downtime produksi


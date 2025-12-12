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

### 2. Build Assets untuk Production
```bash
npm install
npm run build
```

Ini akan membuat file di folder `public/build/` yang digunakan di production.

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


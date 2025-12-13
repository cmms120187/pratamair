# Quick Fix: Assets 404 Error di Production

## Masalah
Error: `GET https://tpmir2.tpmcmms.id/build/assets/app-DlVnSmAl.css net::ERR_ABORTED 404 (Not Found)`

Ini berarti assets belum di-build di production.

## Solusi Cepat

### Via SSH di Hostinger:

```bash
# 1. Pastikan di directory project
cd public_html

# 2. Cek apakah Node.js tersedia
node --version
npm --version

# 3. Install dependencies (jika belum)
npm install

# 4. Build assets
npm run build

# 5. Verifikasi file sudah dibuat
ls -la public/build/assets/

# 6. Set permission (jika perlu)
chmod -R 755 public/build

# 7. Clear cache
php artisan view:clear
php artisan config:clear
```

### Jika Node.js tidak tersedia di Hostinger:

**Opsi A: Build di Local, Upload ke Production**

1. Di local (development):
```bash
npm run build
```

2. Upload folder `public/build/` ke production via FTP/File Manager

3. Pastikan struktur folder:
```
public/
  build/
    assets/
      app-*.css
      app-*.js
    manifest.json
```

**Opsi B: Request Node.js di Hostinger**

Hubungi support Hostinger untuk mengaktifkan Node.js di server.

## Verifikasi

Setelah build, cek:
- [ ] `public/build/manifest.json` ada
- [ ] `public/build/assets/app-*.css` ada
- [ ] `public/build/assets/app-*.js` ada
- [ ] Permission folder `public/build` adalah 755

## Setelah Fix

Refresh browser dan cek console - seharusnya tidak ada error 404 lagi.



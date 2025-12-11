# Struktur Route yang Telah Direorganisasi

## Overview
File `routes/web.php` yang sebelumnya sangat panjang (280+ baris) telah dipecah menjadi beberapa file route modular berdasarkan modul fungsional untuk memudahkan pengelolaan dan maintenance.

## Struktur File Route Baru

### File Utama
- **`routes/web.php`** - File utama yang meng-include semua route files dan menangani route dasar (dashboard, profile, contact)

### File Route Modular
1. **`routes/location.php`** - Route untuk modul Location
   - Plants, Processes, Lines, Rooms, Room ERP
   - Middleware: Coordinator dan above

2. **`routes/machinary.php`** - Route untuk modul Machinary
   - Systems, Groups, Machine Types, Brands, Models, Machines, Machine ERP, Mutasi, Parts
   - Middleware: Berbeda-beda sesuai kebutuhan

3. **`routes/downtime.php`** - Route untuk modul Downtime
   - Problems, Reasons, Actions, Downtime ERP2, Work Orders, Downtimes
   - Middleware: Berbeda-beda sesuai kebutuhan

4. **`routes/production.php`** - Route untuk modul Production
   - Production Hourly

5. **`routes/users.php`** - Route untuk modul Users
   - Users, Activities, Permissions, Organizational Structure
   - Middleware: Coordinator dan above untuk Users, Admin untuk Permissions

6. **`routes/preventive-maintenance.php`** - Route untuk Preventive Maintenance
   - Scheduling, Controlling, Monitoring, Updating, Reporting

7. **`routes/predictive-maintenance.php`** - Route untuk Predictive Maintenance
   - Scheduling, Controlling, Monitoring, Updating, Reporting

8. **`routes/reports.php`** - Route untuk Reports & Analytics
   - MTTR & MTBF, Pareto Mesin, Summary Downtime, Kinerja Mekanik, Root Cause Analysis
   - Middleware: Group Leader dan above

9. **`routes/standards.php`** - Route untuk Standards
   - Standards CRUD
   - Middleware: Group Leader dan above

10. **`routes/admin.php`** - Route untuk Admin Functions
    - Upload/Download untuk Room ERP, Machine ERP, Part ERP, Downtime ERP2
    - Middleware: Admin only

## Menu Configuration

Menu configuration telah dipindahkan dari `resources/views/layouts/navigation.blade.php` ke **`config/menu.php`** untuk memudahkan pengelolaan menu tanpa perlu mengedit file view.

### Cara Menambah/Mengubah Menu:
1. Edit file `config/menu.php`
2. Tambah atau ubah item menu di array `menu_groups`
3. Menu akan otomatis ter-load di navigation

### Struktur Menu Item:
```php
[
    'name' => 'Menu Name',
    'route' => 'route.name', // untuk single menu, atau path '/path' untuk group children
    'icon' => 'icon-name',
    'type' => 'single' | 'group',
    'menu_key' => 'menu-key-for-permissions',
    'children' => [...] // hanya untuk type 'group'
]
```

## Keuntungan Struktur Baru

1. **Lebih Mudah Dikelola**: Setiap modul memiliki file route sendiri
2. **Lebih Mudah Dibaca**: File route lebih kecil dan fokus pada satu modul
3. **Lebih Mudah Di-maintain**: Perubahan pada satu modul tidak mempengaruhi file route lainnya
4. **Menu Configuration Terpisah**: Menu configuration terpisah dari view, lebih mudah diubah
5. **Scalable**: Mudah menambah modul baru tanpa membuat file route utama menjadi terlalu panjang

## Catatan Penting

- Semua route files di-include di dalam `Route::middleware('auth')->group()` di `routes/web.php`
- Beberapa route memiliki middleware tambahan sesuai kebutuhan (role-based)
- Urutan include route files di `routes/web.php` tidak mempengaruhi routing karena Laravel menggunakan route name
- Menu configuration menggunakan route name untuk single menu dan path untuk group children


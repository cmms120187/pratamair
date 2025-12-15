# Dummy Data Seeder

Seeder ini akan membuat data dummy untuk:
1. **DowntimeErp2** - Data downtime mesin (3 bulan terakhir)
2. **ProductionDailyGrade** - Hasil produksi per hari (2 bulan terakhir)
3. **ProductionHourly** - Hasil produksi per jam
4. **ProductionDailyDowntime** - Downtime produksi (30% dari data produksi)
5. **WorkOrder** - Work orders (50 records, 2 bulan terakhir)

## Catatan Penting

- **Standard data TIDAK diubah** - Seeder ini hanya menambahkan data dummy, tidak mengubah data standard yang sudah ada
- Seeder ini memerlukan data dasar yang sudah ada:
  - BasicDataSeeder (plants, processes, lines, dll)
  - MachineErpSeeder
  - UsersSeeder
  - RoomErp dengan category 'Production'

## Cara Menjalankan

### 1. Jalankan semua seeder (termasuk dummy data):
```bash
php artisan db:seed
```

### 2. Hanya jalankan dummy data seeder:
```bash
php artisan db:seed --class=DummyDataSeeder
```

### 3. Reset database dan seed ulang:
```bash
php artisan migrate:fresh --seed
```

## Data yang Dihasilkan

### DowntimeErp2
- **Periode**: 3 bulan terakhir
- **Jumlah**: 2-5 downtime per hari
- **Include OEE**: 70% Yes, 30% No
- **Durasi**: 15-180 menit
- **Data**: Plant, Process, Line, Machine, Problem, Reason, Action, dll

### Production Data
- **Periode**: 2 bulan terakhir
- **Target per jam**: 100-500 unit
- **Jam produksi**: 6-12 jam per hari
- **Break duration**: 1 jam (Mon-Thu), 1.5 jam (Fri)
- **Grade A**: 80-95% dari target
- **Grade B**: 2-8% dari Grade A
- **Grade C**: 1-5% dari Grade A
- **Production Hourly**: Data per jam (1-12 jam)

### ProductionDailyDowntime
- **Probabilitas**: 30% dari data produksi
- **Durasi**: 15-120 menit
- **Include OEE**: 80% Yes, 20% No
- **Jenis**: Random dari 11 jenis downtime

### Work Orders
- **Jumlah**: 50 work orders
- **Periode**: 2 bulan terakhir
- **Status**: Random (pending, in_progress, waiting_parts, order_parts, completed, cancelled)
- **Priority**: Random (low, medium, high, urgent)

## Menghapus Data Dummy

Jika ingin menghapus data dummy:

```bash
php artisan tinker
```

```php
// Hapus semua data dummy
\App\Models\DowntimeErp2::truncate();
\App\Models\ProductionDailyGrade::truncate();
\App\Models\ProductionHourly::truncate();
\App\Models\ProductionDailyDowntime::truncate();
\App\Models\WorkOrder::truncate();
```

**PERHATIAN**: Perintah `truncate()` akan menghapus SEMUA data, bukan hanya dummy data. Gunakan dengan hati-hati!

## Troubleshooting

### Error: "No production rooms found"
- Pastikan sudah menjalankan `BasicDataSeeder` dan `MachineErpSeeder`
- Pastikan ada `RoomErp` dengan `category = 'Production'`

### Error: "No machines found"
- Pastikan sudah menjalankan `MachineErpSeeder`

### Error: "No users found"
- Pastikan sudah menjalankan `UsersSeeder`

### Data tidak muncul di grafik OEE
- Pastikan ada data `DowntimeErp2` dengan `include_oee = true`
- Pastikan ada data `ProductionDailyDowntime` dengan `include_oee = true`
- Pastikan tanggal data berada dalam rentang filter





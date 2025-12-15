# Panduan Status Predictive Maintenance

## Status Execution Predictive Maintenance

Sistem Predictive Maintenance menggunakan 5 status untuk tracking execution (pelaksanaan) maintenance:

### 1. **PENDING** (Menunggu)
**Status Default** - Status awal ketika execution baru dibuat

**Karakteristik:**
- Execution sudah dibuat berdasarkan schedule
- Belum dimulai pelaksanaan maintenance
- `actual_start_time` = `null`
- `actual_end_time` = `null`
- `performed_by` = `null` (belum ada yang mengerjakan)
- `measured_value` = `null` (belum ada pengukuran)

**Kapan digunakan:**
- Ketika schedule menghasilkan execution baru
- Jadwal maintenance sudah tiba tapi belum dikerjakan
- Execution yang overdue (terlambat) tetap berstatus pending sampai dikerjakan

**Transisi Status:**
- `pending` → `in_progress` (ketika mulai mengerjakan)
- `pending` → `skipped` (jika dilewati)
- `pending` → `cancelled` (jika dibatalkan)

---

### 2. **IN_PROGRESS** (Sedang Dikerjakan)
**Status Aktif** - Maintenance sedang dalam proses pengerjaan

**Karakteristik:**
- Maintenance sudah dimulai
- `actual_start_time` = **WAJIB diisi** (waktu mulai)
- `actual_end_time` = `null` (belum selesai)
- `performed_by` = **WAJIB diisi** (user yang mengerjakan)
- `measured_value` = bisa sudah diisi atau masih `null`
- `status` = `in_progress`

**Kapan digunakan:**
- Ketika teknisi/mekanik mulai mengerjakan maintenance
- Saat sedang melakukan pengukuran atau inspeksi
- Maintenance belum selesai tapi sudah dimulai

**Transisi Status:**
- `in_progress` → `completed` (ketika selesai)
- `in_progress` → `cancelled` (jika dibatalkan di tengah proses)

**Catatan Penting:**
- Sistem **WAJIB** mengisi `actual_start_time` ketika status diubah ke `in_progress`
- Jika status diubah ke `in_progress` tanpa `actual_start_time`, sistem akan otomatis set ke waktu sekarang

---

### 3. **COMPLETED** (Selesai)
**Status Final** - Maintenance sudah selesai dikerjakan

**Karakteristik:**
- Maintenance sudah selesai
- `actual_start_time` = **WAJIB diisi** (waktu mulai)
- `actual_end_time` = **WAJIB diisi** (waktu selesai)
- `performed_by` = **WAJIB diisi** (user yang mengerjakan)
- `measured_value` = **WAJIB diisi** (nilai pengukuran)
- `measurement_status` = otomatis dihitung berdasarkan `measured_value` vs `standard`
- `status` = `completed`

**Kapan digunakan:**
- Ketika semua pekerjaan maintenance sudah selesai
- Pengukuran sudah dilakukan dan dicatat
- Checklist sudah diselesaikan (jika ada)
- Foto before/after sudah diambil (jika diperlukan)

**Transisi Status:**
- `completed` = **STATUS FINAL** (tidak bisa diubah ke status lain)
- Execution dengan status `completed` tidak bisa diubah lagi

**Validasi Wajib:**
- Sistem **WAJIB** memastikan `measured_value` tidak kosong
- Sistem **WAJIB** mengisi `actual_end_time` jika belum ada
- Jika status diubah ke `completed` tanpa `measured_value`, sistem akan menolak

**Catatan:**
- Status `completed` adalah status final untuk execution
- Execution yang sudah `completed` akan muncul di laporan dan history
- `measurement_status` akan otomatis dihitung: `normal`, `warning`, atau `critical`

---

### 4. **SKIPPED** (Dilewati)
**Status Final** - Maintenance dilewati/diabaikan

**Karakteristik:**
- Maintenance tidak dikerjakan pada jadwal yang ditentukan
- `actual_start_time` = `null` (tidak pernah dimulai)
- `actual_end_time` = `null` (tidak pernah selesai)
- `performed_by` = bisa diisi atau `null`
- `measured_value` = `null` (tidak ada pengukuran)
- `status` = `skipped`

**Kapan digunakan:**
- Ketika maintenance tidak bisa dikerjakan karena alasan tertentu
- Mesin tidak tersedia saat jadwal
- Maintenance point tidak bisa diakses
- Alasan teknis lainnya yang membuat maintenance tidak bisa dilakukan

**Transisi Status:**
- `skipped` = **STATUS FINAL** (tidak bisa diubah ke status lain)
- Execution dengan status `skipped` tidak bisa diubah lagi

**Catatan:**
- Status `skipped` berbeda dengan `cancelled`
- `skipped` = maintenance tidak dikerjakan tapi schedule tetap aktif
- `cancelled` = maintenance dibatalkan dan mungkin schedule juga dibatalkan

---

### 5. **CANCELLED** (Dibatalkan)
**Status Final** - Maintenance dibatalkan

**Karakteristik:**
- Maintenance dibatalkan secara sengaja
- `actual_start_time` = bisa diisi atau `null`
- `actual_end_time` = `null` (tidak pernah selesai)
- `performed_by` = bisa diisi atau `null`
- `measured_value` = `null` (tidak ada pengukuran)
- `status` = `cancelled`

**Kapan digunakan:**
- Ketika maintenance dibatalkan karena alasan bisnis
- Schedule maintenance diubah atau dihapus
- Mesin tidak lagi memerlukan maintenance tersebut
- Alasan administratif lainnya

**Transisi Status:**
- `cancelled` = **STATUS FINAL** (tidak bisa diubah ke status lain)
- Execution dengan status `cancelled` tidak bisa diubah lagi

**Catatan:**
- Status `cancelled` berbeda dengan `skipped`
- `cancelled` = maintenance dibatalkan secara permanen
- `skipped` = maintenance dilewati untuk jadwal ini, tapi schedule tetap aktif

---

## Perbandingan Status

| Status | Start Time | End Time | Measured Value | Performed By | Bisa Diubah? | Status Final? |
|--------|------------|----------|----------------|--------------|--------------|----------------|
| **PENDING** | ❌ Null | ❌ Null | ❌ Null | ❌ Null | ✅ Ya | ❌ Tidak |
| **IN_PROGRESS** | ✅ Wajib | ❌ Null | ⚠️ Optional | ✅ Wajib | ✅ Ya | ❌ Tidak |
| **COMPLETED** | ✅ Wajib | ✅ Wajib | ✅ Wajib | ✅ Wajib | ❌ Tidak | ✅ Ya |
| **SKIPPED** | ❌ Null | ❌ Null | ❌ Null | ⚠️ Optional | ❌ Tidak | ✅ Ya |
| **CANCELLED** | ⚠️ Optional | ❌ Null | ❌ Null | ⚠️ Optional | ❌ Tidak | ✅ Ya |

---

## Flow Status (State Machine)

```
PENDING
  ├──→ IN_PROGRESS
  │      ├──→ COMPLETED ✅ (Final)
  │      └──→ CANCELLED ✅ (Final)
  ├──→ SKIPPED ✅ (Final)
  └──→ CANCELLED ✅ (Final)
```

**Aturan:**
1. Status **PENDING** bisa diubah ke semua status lain
2. Status **IN_PROGRESS** hanya bisa diubah ke `completed` atau `cancelled`
3. Status **COMPLETED**, **SKIPPED**, dan **CANCELLED** adalah **STATUS FINAL** dan tidak bisa diubah lagi

---

## Measurement Status (Status Pengukuran)

Selain status execution, ada juga `measurement_status` yang otomatis dihitung berdasarkan `measured_value` vs `standard`:

- **NORMAL** - Nilai pengukuran dalam batas normal
- **WARNING** - Nilai pengukuran mendekati batas peringatan
- **CRITICAL** - Nilai pengukuran melebihi batas kritis

**Catatan:** `measurement_status` hanya bisa diisi jika:
- `status` = `completed`
- `measured_value` sudah diisi
- `standard` sudah terdefinisi untuk schedule tersebut

---

## Best Practices

### 1. **Workflow yang Disarankan:**
```
PENDING → IN_PROGRESS → COMPLETED
```

### 2. **Kapan Menggunakan SKIPPED:**
- Gunakan `skipped` jika maintenance tidak bisa dikerjakan karena alasan teknis
- Schedule tetap aktif untuk jadwal berikutnya

### 3. **Kapan Menggunakan CANCELLED:**
- Gunakan `cancelled` jika maintenance dibatalkan secara permanen
- Biasanya schedule juga diubah menjadi `inactive` atau `cancelled`

### 4. **Validasi Penting:**
- ✅ Pastikan `actual_start_time` diisi saat status `in_progress`
- ✅ Pastikan `actual_end_time` dan `measured_value` diisi saat status `completed`
- ✅ Pastikan `performed_by` diisi saat status `in_progress` atau `completed`

---

## Contoh Penggunaan

### Contoh 1: Maintenance Normal
```
1. Schedule membuat execution dengan status PENDING
2. Teknisi mulai kerja → status menjadi IN_PROGRESS, actual_start_time = sekarang
3. Teknisi selesai kerja → status menjadi COMPLETED, actual_end_time = sekarang, measured_value = 50.5
4. Sistem otomatis hitung measurement_status = NORMAL (jika 50.5 dalam batas normal)
```

### Contoh 2: Maintenance Dilewati
```
1. Schedule membuat execution dengan status PENDING
2. Mesin tidak tersedia → status menjadi SKIPPED
3. Schedule tetap aktif untuk jadwal berikutnya
```

### Contoh 3: Maintenance Dibatalkan
```
1. Schedule membuat execution dengan status PENDING
2. Schedule diubah menjadi inactive → status menjadi CANCELLED
3. Schedule tidak akan membuat execution lagi
```

---

## Troubleshooting

### Q: Kenapa tidak bisa ubah status dari COMPLETED?
**A:** Status `completed` adalah status final dan tidak bisa diubah. Jika ada kesalahan, buat execution baru atau hubungi admin.

### Q: Apa beda SKIPPED dan CANCELLED?
**A:** 
- `skipped` = maintenance dilewati untuk jadwal ini, schedule tetap aktif
- `cancelled` = maintenance dibatalkan, biasanya schedule juga diubah

### Q: Kenapa harus isi measured_value saat COMPLETED?
**A:** Karena predictive maintenance memerlukan data pengukuran untuk analisis dan perbandingan dengan standard.

### Q: Bisa ubah status dari IN_PROGRESS ke PENDING?
**A:** Tidak disarankan. Jika ada kesalahan, lebih baik cancel dan buat execution baru.

---

**Dokumen ini dibuat untuk membantu memahami status di sistem Predictive Maintenance CMMS.**


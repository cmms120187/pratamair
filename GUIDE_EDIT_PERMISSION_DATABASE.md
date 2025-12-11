# Panduan Edit Manual Permission di Database SQL

## Struktur Tabel `role_permissions`

```sql
CREATE TABLE `role_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `menu_key` varchar(100) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `role_permissions_role_menu_key_unique` (`role`, `menu_key`),
  KEY `role_permissions_role_index` (`role`),
  KEY `role_permissions_menu_key_index` (`menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Cara Akses Database

### Metode 1: phpMyAdmin (Paling Mudah)
1. Buka browser, akses: `http://localhost/phpmyadmin`
2. Login dengan username/password MySQL (default: root/kosong)
3. Pilih database aplikasi Anda (cek di file `.env` → `DB_DATABASE`)
4. Klik tabel `role_permissions` di sidebar kiri

### Metode 2: MySQL Command Line
1. Buka Command Prompt/Terminal
2. Masuk ke direktori XAMPP MySQL:
   ```bash
   cd C:\xampp\mysql\bin
   ```
3. Login ke MySQL:
   ```bash
   mysql -u root -p
   ```
   (Tekan Enter jika tidak ada password, atau masukkan password jika ada)
4. Pilih database:
   ```sql
   USE nama_database_anda;
   ```
   (Ganti `nama_database_anda` dengan nama database dari file `.env`)

---

## Query SQL untuk Melihat Data Permission

### 1. Lihat Semua Permission
```sql
SELECT * FROM role_permissions ORDER BY role, menu_key;
```

### 2. Lihat Permission untuk Role Tertentu
```sql
-- Contoh: Lihat semua permission untuk team_leader
SELECT * FROM role_permissions WHERE role = 'team_leader';
```

### 3. Lihat Permission untuk Menu Tertentu
```sql
-- Contoh: Lihat siapa saja yang punya akses ke menu 'problems'
SELECT * FROM role_permissions WHERE menu_key = 'problems';
```

### 4. Lihat Permission Spesifik
```sql
-- Contoh: Cek apakah team_leader punya akses ke problems
SELECT * FROM role_permissions 
WHERE role = 'team_leader' AND menu_key = 'problems';
```

---

## Query SQL untuk Menambah Permission

### 1. Tambah Single Permission
```sql
-- Format: INSERT INTO role_permissions (role, menu_key, allowed, created_at, updated_at)
-- Contoh: Berikan akses 'problems' ke 'team_leader'
INSERT INTO role_permissions (role, menu_key, allowed, created_at, updated_at)
VALUES ('team_leader', 'problems', 1, NOW(), NOW());
```

### 2. Tambah Multiple Permissions untuk Satu Role
```sql
-- Contoh: Berikan akses problems, reasons, actions ke team_leader
INSERT INTO role_permissions (role, menu_key, allowed, created_at, updated_at) VALUES
('team_leader', 'problems', 1, NOW(), NOW()),
('team_leader', 'reasons', 1, NOW(), NOW()),
('team_leader', 'actions', 1, NOW(), NOW());
```

### 3. Tambah Permission untuk Multiple Roles
```sql
-- Contoh: Berikan akses 'problems' ke team_leader, group_leader, coordinator
INSERT INTO role_permissions (role, menu_key, allowed, created_at, updated_at) VALUES
('team_leader', 'problems', 1, NOW(), NOW()),
('group_leader', 'problems', 1, NOW(), NOW()),
('coordinator', 'problems', 1, NOW(), NOW());
```

---

## Query SQL untuk Menghapus Permission

### 1. Hapus Single Permission
```sql
-- Contoh: Hapus akses 'problems' dari 'team_leader'
DELETE FROM role_permissions 
WHERE role = 'team_leader' AND menu_key = 'problems';
```

### 2. Hapus Multiple Permissions untuk Satu Role
```sql
-- Contoh: Hapus akses problems, reasons, actions dari team_leader
DELETE FROM role_permissions 
WHERE role = 'team_leader' 
AND menu_key IN ('problems', 'reasons', 'actions');
```

### 3. Hapus Semua Permission untuk Satu Role
```sql
-- Contoh: Hapus semua permission untuk team_leader
DELETE FROM role_permissions WHERE role = 'team_leader';
```

### 4. Hapus Semua Permission untuk Satu Menu
```sql
-- Contoh: Hapus akses 'problems' dari semua role
DELETE FROM role_permissions WHERE menu_key = 'problems';
```

### 5. Hapus Semua Permission (Kosongkan Tabel)
```sql
DELETE FROM role_permissions;
-- atau
TRUNCATE TABLE role_permissions;
```

---

## Query SQL untuk Update Permission

### 1. Update Single Permission
```sql
-- Contoh: Ubah akses 'problems' untuk 'team_leader' menjadi allowed (1)
UPDATE role_permissions 
SET allowed = 1, updated_at = NOW()
WHERE role = 'team_leader' AND menu_key = 'problems';
```

### 2. Update Multiple Permissions
```sql
-- Contoh: Berikan akses problems, reasons, actions ke team_leader
-- (Gunakan INSERT ... ON DUPLICATE KEY UPDATE untuk insert atau update)
INSERT INTO role_permissions (role, menu_key, allowed, created_at, updated_at) VALUES
('team_leader', 'problems', 1, NOW(), NOW()),
('team_leader', 'reasons', 1, NOW(), NOW()),
('team_leader', 'actions', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  allowed = VALUES(allowed), 
  updated_at = NOW();
```

### 3. Ubah Status Allowed (Enable/Disable)
```sql
-- Enable permission (allowed = 1)
UPDATE role_permissions 
SET allowed = 1, updated_at = NOW()
WHERE role = 'team_leader' AND menu_key = 'problems';

-- Disable permission (allowed = 0)
UPDATE role_permissions 
SET allowed = 0, updated_at = NOW()
WHERE role = 'team_leader' AND menu_key = 'problems';
```

---

## Contoh Kasus: Menghapus Permission Problems, Reasons, Actions untuk Team Leader

```sql
-- Hapus permission problems, reasons, actions untuk team_leader
DELETE FROM role_permissions 
WHERE role = 'team_leader' 
AND menu_key IN ('problems', 'reasons', 'actions');
```

---

## Contoh Kasus: Menambah Permission Problems, Reasons, Actions untuk Group Leader

```sql
-- Tambah permission problems, reasons, actions untuk group_leader
INSERT INTO role_permissions (role, menu_key, allowed, created_at, updated_at) VALUES
('group_leader', 'problems', 1, NOW(), NOW()),
('group_leader', 'reasons', 1, NOW(), NOW()),
('group_leader', 'actions', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  allowed = VALUES(allowed), 
  updated_at = NOW();
```

---

## Daftar Role yang Tersedia

```sql
-- Cek role yang ada di sistem
SELECT DISTINCT role FROM role_permissions ORDER BY role;
```

Role yang umum digunakan:
- `mekanik`
- `team_leader`
- `group_leader`
- `coordinator`
- `ast_manager`
- `manager`
- `general_manager`
- `admin` (tidak perlu permission, selalu punya akses penuh)

---

## Daftar Menu Key yang Tersedia

```sql
-- Cek menu_key yang ada di sistem
SELECT DISTINCT menu_key FROM role_permissions ORDER BY menu_key;
```

Menu key yang umum:
- `dashboard`
- `location`, `plants`, `processes`, `lines`, `room-erp`
- `machinary`, `systems`, `groups`, `machine-types`, `brands`, `models`, `machine-erp`, `mutasi`
- `downtime`, `problems`, `reasons`, `actions`, `downtime-erp2`, `work-orders`
- `users`, `users-list`, `organizational-structure`, `activities`
- `preventive-maintenance`, `preventive-scheduling`, `preventive-controlling`, `preventive-monitoring`, `preventive-updating`, `preventive-reporting`
- `predictive-maintenance`, `standards`, `predictive-scheduling`, `predictive-controlling`, `predictive-monitoring`, `predictive-updating`, `predictive-reporting`
- `reports`, `mttr-mtbf`, `pareto-machine`, `summary-downtime`, `mechanic-performance`, `root-cause-analysis`, `part-erp`

---

## Tips & Peringatan

### ✅ Tips:
1. **Selalu backup database sebelum edit manual**
   ```sql
   -- Export tabel role_permissions
   SELECT * FROM role_permissions INTO OUTFILE 'role_permissions_backup.sql';
   ```

2. **Gunakan transaksi untuk operasi multiple**
   ```sql
   START TRANSACTION;
   -- Query Anda di sini
   DELETE FROM role_permissions WHERE role = 'team_leader' AND menu_key IN ('problems', 'reasons', 'actions');
   -- Jika sudah benar, commit
   COMMIT;
   -- Jika ada kesalahan, rollback
   -- ROLLBACK;
   ```

3. **Cek dulu sebelum hapus/update**
   ```sql
   -- Cek dulu data yang akan dihapus
   SELECT * FROM role_permissions 
   WHERE role = 'team_leader' AND menu_key IN ('problems', 'reasons', 'actions');
   ```

### ⚠️ Peringatan:
1. **Jangan hapus semua data tanpa backup**
2. **Pastikan role dan menu_key yang digunakan sudah benar (case-sensitive)**
3. **Pastikan nilai `allowed` adalah 1 (true) atau 0 (false), bukan string 'true'/'false'**
4. **Setelah edit manual, refresh halaman permission di aplikasi untuk melihat perubahan**

---

## Troubleshooting

### Error: Duplicate entry
Jika mendapat error "Duplicate entry", berarti permission sudah ada. Gunakan `UPDATE` atau `INSERT ... ON DUPLICATE KEY UPDATE`:
```sql
INSERT INTO role_permissions (role, menu_key, allowed, created_at, updated_at)
VALUES ('team_leader', 'problems', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  allowed = VALUES(allowed), 
  updated_at = NOW();
```

### Permission tidak muncul setelah edit
1. Clear cache aplikasi:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```
2. Refresh halaman permission di browser (Ctrl+F5)

### Cek apakah permission sudah tersimpan
```sql
SELECT * FROM role_permissions 
WHERE role = 'team_leader' AND menu_key = 'problems';
```


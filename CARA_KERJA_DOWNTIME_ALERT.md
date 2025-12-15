# Cara Kerja Downtime Alert 🔔

## 📋 Overview

Downtime Alert adalah sistem notifikasi otomatis yang akan mengirimkan alert (email dan in-app notification) ketika terjadi downtime yang memenuhi kriteria tertentu (misalnya: duration > 1 jam).

---

## 🔄 Alur Kerja

```
1. User membuat/update downtime
   ↓
2. Controller check kondisi (duration > threshold)
   ↓
3. Controller kirim notification ke user yang relevan
   ↓
4. Notification masuk ke queue (background job)
   ↓
5. Queue worker process notification
   ↓
6. Email dikirim + Database notification disimpan
   ↓
7. User menerima email & melihat notification di aplikasi
```

---

## 🏗️ Komponen Sistem

### 1. **Notification Class** (`app/Notifications/DowntimeAlert.php`)

**Fungsi:**
- Mengatur format email notification
- Menyimpan data ke database untuk in-app notification
- Support untuk 3 jenis downtime: `downtime_erp2`, `downtime_erp`, `downtime`

**Channels:**
- `mail` - Email notification
- `database` - In-app notification (disimpan di tabel `notifications`)

**Queue:**
- Menggunakan `ShouldQueue` - notification diproses di background
- Tidak blocking request user

### 2. **Controller Integration**

Notification dipanggil dari controller saat:
- Create downtime baru
- Update downtime yang sudah ada
- Import downtime dari CSV/Excel

### 3. **Queue Worker**

Background process yang menjalankan notification:
```bash
php artisan queue:work
```

---

## 💻 Cara Implementasi

### **Contoh 1: Implementasi di DowntimeErp2Controller**

Tambahkan di method `store()` dan `update()`:

```php
use App\Notifications\DowntimeAlert;
use App\Models\User;

public function store(Request $request)
{
    // ... existing validation and creation code ...
    
    $downtime = DowntimeErp2::create($validated);
    
    // ========== DOWNTIME ALERT ==========
    // Parse duration untuk mendapatkan menit
    $durationStr = $downtime->duration ?? '';
    $durationMinutes = 0;
    
    if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1];
    } elseif (preg_match('/(\d+)\s*hours?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1] * 60;
    }
    
    // Kirim alert jika duration > 60 menit (1 jam)
    if ($durationMinutes > 60) {
        // Get users yang perlu di-notify (managers, coordinators)
        $notifyUsers = User::whereIn('role', [
            'manager', 
            'general_manager', 
            'coordinator',
            'ast_manager'
        ])->get();
        
        foreach ($notifyUsers as $user) {
            $user->notify(new DowntimeAlert($downtime, 'downtime_erp2', $durationMinutes));
        }
    }
    // ========== END DOWNTIME ALERT ==========
    
    return redirect()->route('downtime-erp2.index')->with('success', 'Downtime ERP2 created successfully.');
}
```

### **Contoh 2: Implementasi di DowntimeController**

```php
use App\Notifications\DowntimeAlert;
use App\Models\User;

public function store(Request $request)
{
    // ... existing code ...
    
    $downtime = new \App\Models\Downtime();
    $downtime->fill($validated);
    $downtime->save();
    
    // ========== DOWNTIME ALERT ==========
    // Duration sudah dalam menit (dari calculation sebelumnya)
    $durationMinutes = $downtime->duration ?? 0;
    
    // Kirim alert jika duration > 60 menit
    if ($durationMinutes > 60) {
        // Notify managers dan coordinators
        $notifyUsers = User::whereIn('role', [
            'manager', 
            'general_manager', 
            'coordinator'
        ])->get();
        
        foreach ($notifyUsers as $user) {
            $user->notify(new DowntimeAlert($downtime, 'downtime', $durationMinutes));
        }
    }
    // ========== END DOWNTIME ALERT ==========
    
    return redirect()->route('downtimes.index')->with('success', 'Downtime created successfully.');
}
```

### **Contoh 3: Alert Berdasarkan Kriteria Lain**

Bisa juga mengirim alert berdasarkan kriteria lain:

```php
// Alert jika problem tertentu
if (in_array($downtime->problemDowntime, ['Critical Failure', 'Safety Issue'])) {
    // Notify semua managers
    $managers = User::whereIn('role', ['manager', 'general_manager'])->get();
    foreach ($managers as $manager) {
        $manager->notify(new DowntimeAlert($downtime, 'downtime_erp2'));
    }
}

// Alert jika machine tertentu
if (in_array($downtime->idMachine, ['MACHINE-001', 'MACHINE-002'])) {
    // Notify specific users
    $users = User::whereIn('email', ['supervisor@company.com'])->get();
    foreach ($users as $user) {
        $user->notify(new DowntimeAlert($downtime, 'downtime_erp2'));
    }
}

// Alert jika duration > 2 jam (critical)
if ($durationMinutes > 120) {
    // Notify semua level management
    $criticalUsers = User::whereIn('role', [
        'manager', 
        'general_manager', 
        'coordinator',
        'ast_manager'
    ])->get();
    
    foreach ($criticalUsers as $user) {
        $user->notify(new DowntimeAlert($downtime, 'downtime_erp2', $durationMinutes));
    }
}
```

---

## ⚙️ Setup & Konfigurasi

### 1. **Setup Mail Configuration**

Pastikan mail sudah dikonfigurasi di `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. **Setup Queue**

**Option A: Database Queue (Recommended untuk development)**
```env
QUEUE_CONNECTION=database
```

**Option B: Sync (Untuk testing - tidak perlu queue worker)**
```env
QUEUE_CONNECTION=sync
```

**Option C: Redis (Untuk production)**
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. **Create Queue Table (jika menggunakan database queue)**

```bash
php artisan queue:table
php artisan migrate
```

### 4. **Run Queue Worker**

**Development:**
```bash
php artisan queue:work
```

**Production (dengan supervisor):**
Setup supervisor untuk auto-restart queue worker.

---

## 📧 Format Email Notification

Email yang dikirim akan berisi:

```
Subject: Downtime Alert: {Machine} - {Problem}

Body:
**Downtime Alert Detected**
A new downtime has been recorded in the system.

**Machine:** MACHINE-001 - Injection Molding
**Location:** Plant A > Process 1 > Line 1
**Problem:** Motor Failure
**Duration:** 2 hours 30 minutes
**Date:** 15 Dec 2025 14:30

[View Details Button]

This is an automated notification from TPM ERP OEE System.
```

---

## 💾 Database Notification

Notification juga disimpan di tabel `notifications` dengan struktur:

```json
{
  "downtime_id": 123,
  "downtime_type": "downtime_erp2",
  "machine": "MACHINE-001 - Injection Molding",
  "problem": "Motor Failure",
  "duration": "2 hours 30 minutes",
  "location": "Plant A > Process 1 > Line 1",
  "date": "15 Dec 2025 14:30"
}
```

User bisa melihat notification ini di aplikasi (jika ada fitur notification center).

---

## 🎯 Customization

### **Mengubah Threshold Duration**

Ganti `60` dengan nilai lain:

```php
// Alert jika > 30 menit
if ($durationMinutes > 30) { ... }

// Alert jika > 2 jam
if ($durationMinutes > 120) { ... }
```

### **Mengubah User yang Di-notify**

```php
// Hanya managers
$notifyUsers = User::where('role', 'manager')->get();

// Specific users by email
$notifyUsers = User::whereIn('email', [
    'manager1@company.com',
    'manager2@company.com'
])->get();

// Users berdasarkan plant/location
$notifyUsers = User::whereHas('plants', function($q) use ($downtime) {
    $q->where('name', $downtime->plant);
})->get();
```

### **Mengubah Email Template**

Edit method `toMail()` di `app/Notifications/DowntimeAlert.php`:

```php
public function toMail(object $notifiable): MailMessage
{
    // Customize email content here
    return (new MailMessage)
        ->subject('Custom Subject')
        ->line('Custom message')
        // ... more customization
}
```

---

## 🧪 Testing

### **Test Notification (Development)**

1. **Gunakan Sync Queue:**
   ```env
   QUEUE_CONNECTION=sync
   ```
   Notification akan langsung diproses (tidak perlu queue worker).

2. **Create Test Downtime:**
   - Buat downtime dengan duration > 60 menit
   - Check email inbox
   - Check tabel `notifications` di database

3. **Test dengan Tinker:**
   ```bash
   php artisan tinker
   ```
   ```php
   $downtime = App\Models\DowntimeErp2::first();
   $user = App\Models\User::where('role', 'manager')->first();
   $user->notify(new App\Notifications\DowntimeAlert($downtime, 'downtime_erp2', 90));
   ```

### **Check Queue Status**

```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

---

## 📊 Monitoring

### **Check Notification Status**

```sql
-- Check notifications di database
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;

-- Check queue jobs
SELECT * FROM jobs ORDER BY created_at DESC LIMIT 10;

-- Check failed jobs
SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 10;
```

### **Laravel Telescope (Optional)**

Install Laravel Telescope untuk monitoring:
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

---

## ⚠️ Troubleshooting

### **Email tidak terkirim:**
1. Check mail configuration di `.env`
2. Test dengan `php artisan tinker`:
   ```php
   Mail::raw('Test email', function($msg) {
       $msg->to('your-email@example.com')->subject('Test');
   });
   ```
3. Check queue worker berjalan: `php artisan queue:work`

### **Notification tidak masuk queue:**
1. Check `QUEUE_CONNECTION` di `.env`
2. Pastikan queue table sudah dibuat: `php artisan queue:table && php artisan migrate`
3. Check queue worker berjalan

### **Notification masuk tapi tidak diproses:**
1. Check failed jobs: `php artisan queue:failed`
2. Check logs: `storage/logs/laravel.log`
3. Restart queue worker

---

## 📝 Checklist Implementasi

- [ ] Mail configuration sudah setup di `.env`
- [ ] Queue connection sudah dikonfigurasi
- [ ] Queue table sudah dibuat (jika menggunakan database queue)
- [ ] Queue worker berjalan
- [ ] Notification class sudah dibuat
- [ ] Controller sudah di-update untuk kirim notification
- [ ] Test dengan create downtime > threshold
- [ ] Email terkirim dengan benar
- [ ] Database notification tersimpan

---

## 🎉 Summary

Downtime Alert bekerja dengan:
1. ✅ **Automatic** - Otomatis saat downtime dibuat/update
2. ✅ **Queue-based** - Tidak blocking request user
3. ✅ **Multi-channel** - Email + Database notification
4. ✅ **Flexible** - Bisa dikustomisasi threshold dan user
5. ✅ **Scalable** - Menggunakan queue untuk handle banyak notification

**Status:** ✅ Ready to use!

---

**Dibuat:** 2025-12-15
**Versi:** 1.0


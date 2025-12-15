# 📧 Cara Setting Email Managers untuk Downtime Alert

## 🎯 Overview

Sistem Downtime Alert akan otomatis mengirim email ke managers jika:
- ✅ Duration downtime > 60 menit (configurable)
- ✅ Problem critical (Motor Failure, Safety Issue, dll)
- ✅ Machine critical tertentu

---

## ⚙️ Cara Setting Email Managers

Ada **3 cara** untuk mengatur siapa yang akan menerima email alert:

### **Cara 1: Setting via Environment Variables (.env)** ⭐ **RECOMMENDED**

Edit file `.env` di root project:

```env
# Enable/Disable Alert
DOWNTIME_ALERT_ENABLED=true

# Threshold Duration (dalam menit)
DOWNTIME_ALERT_DURATION_THRESHOLD=60

# Alert by Role (pisahkan dengan koma)
DOWNTIME_ALERT_ROLES=manager,general_manager,coordinator,ast_manager

# Alert by Email (pisahkan dengan koma) - Override roles
DOWNTIME_ALERT_EMAILS=manager1@company.com,manager2@company.com

# Alert by User ID (pisahkan dengan koma) - Override roles
DOWNTIME_ALERT_USER_IDS=1,2,3

# Critical Machines (pisahkan dengan koma)
DOWNTIME_ALERT_CRITICAL_MACHINES=MACHINE-001,MACHINE-002

# Alert Types
DOWNTIME_ALERT_TYPE_DURATION=true
DOWNTIME_ALERT_TYPE_CRITICAL_PROBLEM=true
DOWNTIME_ALERT_TYPE_CRITICAL_MACHINE=true
```

**Contoh Setting:**

```env
# Hanya manager dan general_manager yang terima alert
DOWNTIME_ALERT_ROLES=manager,general_manager

# Atau spesifik email
DOWNTIME_ALERT_EMAILS=production.manager@company.com,maintenance.manager@company.com

# Atau kombinasi role + email
DOWNTIME_ALERT_ROLES=manager
DOWNTIME_ALERT_EMAILS=admin@company.com
```

---

### **Cara 2: Setting via Config File**

Edit file `config/downtime_alert.php`:

```php
return [
    // Threshold duration (menit)
    'duration_threshold' => 60,

    // Critical problems
    'critical_problems' => [
        'Motor Failure',
        'Safety Issue',
        'Electrical Failure',
        'Fire',
        'Emergency Stop',
    ],

    // Critical machines
    'critical_machines' => ['MACHINE-001', 'MACHINE-002'],

    // Recipients
    'recipients' => [
        // By Role
        'roles' => ['manager', 'general_manager', 'coordinator'],
        
        // By Email (override roles)
        'emails' => ['manager1@company.com', 'manager2@company.com'],
        
        // By User ID (override roles)
        'user_ids' => [1, 2, 3],
    ],

    // Enable/Disable
    'enabled' => true,

    // Alert Types
    'alert_types' => [
        'duration' => true,
        'critical_problem' => true,
        'critical_machine' => true,
    ],
];
```

---

### **Cara 3: Setting via Database (Future Enhancement)**

Untuk setting via database, bisa dibuat admin panel untuk manage recipients.

---

## 📋 Contoh Konfigurasi

### **Scenario 1: Alert ke Semua Manager**

```env
DOWNTIME_ALERT_ROLES=manager,general_manager,coordinator,ast_manager
DOWNTIME_ALERT_DURATION_THRESHOLD=60
```

### **Scenario 2: Alert ke Email Spesifik**

```env
DOWNTIME_ALERT_EMAILS=production.manager@company.com,maintenance.manager@company.com,plant.manager@company.com
DOWNTIME_ALERT_DURATION_THRESHOLD=30
```

### **Scenario 3: Alert untuk Machine Critical**

```env
DOWNTIME_ALERT_ROLES=manager,general_manager
DOWNTIME_ALERT_CRITICAL_MACHINES=MACHINE-001,MACHINE-002,MACHINE-003
DOWNTIME_ALERT_DURATION_THRESHOLD=15
```

### **Scenario 4: Alert Hanya untuk Problem Critical**

```env
DOWNTIME_ALERT_ROLES=manager,general_manager
DOWNTIME_ALERT_TYPE_DURATION=false
DOWNTIME_ALERT_TYPE_CRITICAL_PROBLEM=true
DOWNTIME_ALERT_TYPE_CRITICAL_MACHINE=false
```

---

## 🔧 Implementasi di Controller

Gunakan `DowntimeAlertService` untuk mengirim alert:

```php
use App\Services\DowntimeAlertService;

class DowntimeErp2Controller extends Controller
{
    protected $alertService;

    public function __construct(DowntimeAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function store(Request $request)
    {
        // ... validation dan create downtime ...
        
        $downtime = DowntimeErp2::create($validated);
        
        // Parse duration
        $durationMinutes = $this->parseDuration($downtime->duration);
        
        // Send alert (otomatis check threshold, critical problem, critical machine)
        $this->alertService->sendAlert($downtime, 'downtime_erp2', $durationMinutes);
        
        return redirect()->route('downtime-erp2.index')
            ->with('success', 'Downtime created successfully.');
    }

    private function parseDuration($durationStr)
    {
        $minutes = 0;
        if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
            $minutes = (int)$matches[1];
        } elseif (preg_match('/(\d+)\s*hours?/i', $durationStr, $matches)) {
            $minutes = (int)$matches[1] * 60;
        } elseif (preg_match('/(\d+)\s*hours?\s*(\d+)\s*minutes?/i', $durationStr, $matches)) {
            $minutes = (int)$matches[1] * 60 + (int)$matches[2];
        }
        return $minutes;
    }
}
```

---

## 📧 Setup Mail Configuration

Pastikan mail sudah dikonfigurasi di `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="TPM ERP OEE System"
```

---

## ✅ Checklist Setup

- [ ] File `config/downtime_alert.php` sudah dibuat
- [ ] File `app/Services/DowntimeAlertService.php` sudah dibuat
- [ ] Setting `.env` untuk recipients
- [ ] Setting `.env` untuk mail configuration
- [ ] Test dengan membuat downtime > threshold
- [ ] Check email inbox managers
- [ ] Check log untuk troubleshooting

---

## 🧪 Testing

### **Test 1: Alert by Duration**

1. Buat downtime dengan duration > 60 menit
2. Check email inbox managers
3. Check log: `storage/logs/laravel.log`

### **Test 2: Alert by Critical Problem**

1. Buat downtime dengan problem "Motor Failure"
2. Check email inbox managers
3. Alert harus terkirim meskipun duration < 60 menit

### **Test 3: Alert by Critical Machine**

1. Buat downtime untuk machine yang ada di `critical_machines`
2. Check email inbox managers
3. Alert harus terkirim meskipun duration < 60 menit

---

## 🔍 Troubleshooting

### **Problem: Email tidak terkirim**

**Check:**
1. ✅ Mail configuration di `.env` sudah benar
2. ✅ `DOWNTIME_ALERT_ENABLED=true`
3. ✅ Recipients sudah di-set (roles/emails/user_ids)
4. ✅ Queue worker running (jika pakai queue)
5. ✅ Check log: `storage/logs/laravel.log`

### **Problem: Alert terkirim ke semua user**

**Solution:**
- Pastikan hanya set `roles` atau `emails` atau `user_ids`, jangan semua
- Priority: `emails` > `user_ids` > `roles`

### **Problem: Alert tidak terkirim untuk critical problem**

**Solution:**
- Check `critical_problems` di config
- Pastikan nama problem match (case-insensitive)
- Check `DOWNTIME_ALERT_TYPE_CRITICAL_PROBLEM=true`

---

## 📝 Notes

- **Priority Recipients:** `emails` > `user_ids` > `roles`
- **Default Threshold:** 60 menit
- **Default Roles:** manager, general_manager, coordinator, ast_manager
- **Alert akan otomatis check semua kondisi** (duration, critical problem, critical machine)
- **Jika salah satu kondisi terpenuhi, alert akan dikirim**

---

## 🚀 Quick Start

1. **Copy config file:**
   ```bash
   # File sudah dibuat: config/downtime_alert.php
   ```

2. **Edit `.env`:**
   ```env
   DOWNTIME_ALERT_ENABLED=true
   DOWNTIME_ALERT_ROLES=manager,general_manager
   DOWNTIME_ALERT_DURATION_THRESHOLD=60
   ```

3. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

4. **Test:**
   - Buat downtime > 60 menit
   - Check email inbox

---

**Status:** ✅ **READY TO USE**

**File yang dibuat:**
- ✅ `config/downtime_alert.php`
- ✅ `app/Services/DowntimeAlertService.php`
- ✅ `CARA_SETTING_EMAIL_MANAGERS_DOWNTIME_ALERT.md`


# Contoh Implementasi Downtime Alert di Controller

## ⚙️ Setting Email Managers

**Sebelum implementasi, pastikan sudah setting email managers di:**
- 📄 File: `config/downtime_alert.php` atau
- 🔧 Environment: `.env`

**Lihat dokumentasi lengkap:** `CARA_SETTING_EMAIL_MANAGERS_DOWNTIME_ALERT.md`

**Quick Setup (.env):**
```env
DOWNTIME_ALERT_ENABLED=true
DOWNTIME_ALERT_ROLES=manager,general_manager,coordinator
DOWNTIME_ALERT_DURATION_THRESHOLD=60
```

---

## 📝 Implementasi Langsung

Berikut adalah contoh implementasi yang bisa langsung ditambahkan ke controller Anda:

---

## 1. DowntimeErp2Controller - Method store()

**File:** `app/Http/Controllers/DowntimeErp2Controller.php`

Tambahkan setelah line 274 (setelah `DowntimeErp2::create($validated);`):

```php
use App\Services\DowntimeAlertService;

// ... existing code ...

protected $alertService;

public function __construct(DowntimeAlertService $alertService)
{
    $this->alertService = $alertService;
}

public function store(Request $request)
{
    // ... existing validation code ...
    
    $downtime = DowntimeErp2::create($validated);
    
    // ========== DOWNTIME ALERT ==========
    // Parse duration untuk mendapatkan menit
    $durationStr = $downtime->duration ?? '';
    $durationMinutes = 0;
    
    // Parse duration string (format: "X minutes" atau "X hours")
    if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1];
    } elseif (preg_match('/(\d+)\s*hours?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1] * 60;
    } elseif (preg_match('/(\d+)\s*hours?\s*(\d+)\s*minutes?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1] * 60 + (int)$matches[2];
    }
    
    // Send alert (otomatis check threshold, critical problem, critical machine)
    // Recipients diambil dari config/downtime_alert.php
    $this->alertService->sendAlert($downtime, 'downtime_erp2', $durationMinutes);
    // ========== END DOWNTIME ALERT ==========
    
    return redirect()->route('downtime-erp2.index')->with('success', 'Downtime ERP2 created successfully.');
}
```

---

## 2. DowntimeErp2Controller - Method update()

Tambahkan setelah update downtime:

```php
public function update(Request $request, $id)
{
    // ... existing validation and update code ...
    
    $downtime = DowntimeErp2::findOrFail($id);
    $downtime->update($validated);
    
    // ========== DOWNTIME ALERT ==========
    // Parse duration
    $durationStr = $downtime->duration ?? '';
    $durationMinutes = 0;
    
    if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1];
    } elseif (preg_match('/(\d+)\s*hours?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1] * 60;
    } elseif (preg_match('/(\d+)\s*hours?\s*(\d+)\s*minutes?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1] * 60 + (int)$matches[2];
    }
    
    // Send alert (otomatis check threshold, critical problem, critical machine)
    $this->alertService->sendAlert($downtime, 'downtime_erp2', $durationMinutes);
    // ========== END DOWNTIME ALERT ==========
    
    return redirect()->route('downtime-erp2.index')->with('success', 'Downtime ERP2 updated successfully.');
}
```

---

## 3. DowntimeController - Method store()

**File:** `app/Http/Controllers/DowntimeController.php`

Tambahkan setelah line 318 (setelah `$downtime->save();`):

```php
use App\Notifications\DowntimeAlert;
use App\Models\User;

// ... existing code ...

public function store(Request $request)
{
    // ... existing code ...
    
    $downtime = new \App\Models\Downtime();
    $downtime->fill($validated);
    $downtime->save();
    
    // ... existing parts attachment code ...
    
    // ========== DOWNTIME ALERT ==========
    // Duration sudah dalam menit (dari calculation sebelumnya)
    $durationMinutes = $downtime->duration ?? 0;
    
    // Send alert (otomatis check threshold, critical problem, critical machine)
    $this->alertService->sendAlert($downtime, 'downtime', $durationMinutes);
    // ========== END DOWNTIME ALERT ==========
    
    return redirect()->route('downtimes.index')->with('success', 'Downtime created successfully.');
}
```

---

## 4. DowntimeController - Method update()

Tambahkan setelah update downtime:

```php
public function update(Request $request, string $id)
{
    // ... existing validation and update code ...
    
    $downtime = \App\Models\Downtime::findOrFail($id);
    $downtime->update($validated);
    
    // ... existing parts sync code ...
    
    // ========== DOWNTIME ALERT ==========
    $durationMinutes = $downtime->duration ?? 0;
    
    // Send alert (otomatis check threshold, critical problem, critical machine)
    $this->alertService->sendAlert($downtime, 'downtime', $durationMinutes);
    // ========== END DOWNTIME ALERT ==========
    
    return redirect()->route('downtimes.index')->with('success', 'Downtime updated successfully.');
}
```

---

## 5. DowntimeErpController - Method store()

**File:** `app/Http/Controllers/DowntimeErpController.php`

Tambahkan setelah line 230 (setelah `DowntimeErp::create($validated);`):

```php
use App\Services\DowntimeAlertService;

// ... existing code ...

protected $alertService;

public function __construct(DowntimeAlertService $alertService)
{
    $this->alertService = $alertService;
}

public function store(Request $request)
{
    // ... existing validation code ...
    
    $downtime = DowntimeErp::create($validated);
    
    // ========== DOWNTIME ALERT ==========
    $durationStr = $downtime->duration ?? '';
    $durationMinutes = 0;
    
    if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1];
    } elseif (preg_match('/(\d+)\s*hours?/i', $durationStr, $matches)) {
        $durationMinutes = (int)$matches[1] * 60;
    }
    
    // Send alert (otomatis check threshold, critical problem, critical machine)
    $this->alertService->sendAlert($downtime, 'downtime_erp', $durationMinutes);
    // ========== END DOWNTIME ALERT ==========
    
    return redirect()->route('downtime_erp.index')->with('success', 'Downtime ERP created successfully.');
}
```

---

## 🎯 Variasi Implementasi

> **Note:** Untuk implementasi yang lebih mudah, gunakan `DowntimeAlertService` yang sudah otomatis handle semua kondisi (duration, critical problem, critical machine). Setting recipients via `config/downtime_alert.php` atau `.env`.

### **A. Menggunakan Service (Recommended)** ⭐

```php
use App\Services\DowntimeAlertService;

// Di constructor
protected $alertService;

public function __construct(DowntimeAlertService $alertService)
{
    $this->alertService = $alertService;
}

// Di method store/update
$durationMinutes = $this->parseDuration($downtime->duration);
$this->alertService->sendAlert($downtime, 'downtime_erp2', $durationMinutes);
```

**Setting recipients via `.env`:**
```env
DOWNTIME_ALERT_ROLES=manager,general_manager,coordinator
DOWNTIME_ALERT_CRITICAL_PROBLEMS=Motor Failure,Safety Issue,Fire
DOWNTIME_ALERT_CRITICAL_MACHINES=MACHINE-001,MACHINE-002
```

### **B. Manual Implementation (Jika Perlu Custom Logic)**

```php
// Alert jika problem critical
$criticalProblems = ['Motor Failure', 'Safety Issue', 'Fire', 'Explosion'];
if (in_array($downtime->problemDowntime, $criticalProblems)) {
    // Notify semua level management
    $notifyUsers = User::whereIn('role', [
        'manager', 
        'general_manager', 
        'coordinator',
        'ast_manager'
    ])->get();
    
    foreach ($notifyUsers as $user) {
        $user->notify(new DowntimeAlert($downtime, 'downtime_erp2'));
    }
}
```

### **C. Alert Berdasarkan Machine Critical**

```php
// Alert jika machine critical
$criticalMachines = ['MACHINE-001', 'MACHINE-002', 'MACHINE-003'];
if (in_array($downtime->idMachine, $criticalMachines)) {
    $notifyUsers = User::whereIn('role', ['manager', 'general_manager'])->get();
    foreach ($notifyUsers as $user) {
        $user->notify(new DowntimeAlert($downtime, 'downtime_erp2'));
    }
}
```

### **C. Alert Multi-Level (Duration Berbeda)**

```php
$durationMinutes = $this->parseDuration($downtime->duration);

// Level 1: > 60 menit - Notify coordinators
if ($durationMinutes > 60 && $durationMinutes <= 120) {
    $notifyUsers = User::where('role', 'coordinator')->get();
    foreach ($notifyUsers as $user) {
        $user->notify(new DowntimeAlert($downtime, 'downtime_erp2', $durationMinutes));
    }
}

// Level 2: > 2 jam - Notify managers
if ($durationMinutes > 120) {
    $notifyUsers = User::whereIn('role', ['manager', 'general_manager'])->get();
    foreach ($notifyUsers as $user) {
        $user->notify(new DowntimeAlert($downtime, 'downtime_erp2', $durationMinutes));
    }
}
```

### **D. Helper Method untuk Parse Duration**

Tambahkan method helper di controller:

```php
/**
 * Parse duration string to minutes
 */
private function parseDuration(?string $durationStr): int
{
    if (!$durationStr) {
        return 0;
    }
    
    $minutes = 0;
    
    // Format: "X minutes"
    if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
        $minutes = (int)$matches[1];
    }
    // Format: "X hours"
    elseif (preg_match('/(\d+)\s*hours?/i', $durationStr, $matches)) {
        $minutes = (int)$matches[1] * 60;
    }
    // Format: "X hours Y minutes"
    elseif (preg_match('/(\d+)\s*hours?\s*(\d+)\s*minutes?/i', $durationStr, $matches)) {
        $minutes = (int)$matches[1] * 60 + (int)$matches[2];
    }
    
    return $minutes;
}
```

Kemudian gunakan:
```php
$durationMinutes = $this->parseDuration($downtime->duration);
```

---

## ✅ Checklist Implementasi

- [ ] Import `DowntimeAlert` dan `User` di controller
- [ ] Tambahkan code di method `store()`
- [ ] Tambahkan code di method `update()` (optional)
- [ ] Test dengan create downtime > 60 menit
- [ ] Verify email terkirim
- [ ] Verify database notification tersimpan
- [ ] Setup queue worker jika menggunakan queue

---

## 🚀 Quick Start

1. **Copy code** dari contoh di atas
2. **Paste** di controller yang sesuai
3. **Test** dengan membuat downtime > 60 menit
4. **Check** email dan database notifications

**Status:** ✅ Ready to implement!


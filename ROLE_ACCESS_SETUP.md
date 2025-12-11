# Sistem Akses Menu Berbasis Role

Sistem ini memungkinkan pengaturan akses menu dan data yang berbeda untuk setiap level jabatan.

## Level Jabatan (Role Hierarchy)

1. **mekanik** - Level 1 (Terendah)
2. **team_leader** - Level 2
3. **group_leader** - Level 3
4. **coordinator** - Level 4
5. **ast_manager** - Level 5
6. **manager** - Level 6
7. **general_manager** - Level 7 (Tertinggi)

## Komponen Sistem

### 1. PermissionHelper (`app/Helpers/PermissionHelper.php`)

Helper class untuk mengatur permission menu berdasarkan role.

**Fitur:**
- Definisi akses menu per role
- Check akses menu untuk user
- Check level hierarki role
- Get semua menu yang bisa diakses oleh role tertentu

**Cara Menggunakan:**

```php
use App\Helpers\PermissionHelper;

// Check apakah user bisa akses menu
$canAccess = PermissionHelper::canAccessMenu($userRole, 'menu_key');

// Check apakah role user lebih tinggi atau sama dengan role lain
$isHigher = PermissionHelper::isRoleHigherOrEqual($userRole, 'coordinator');

// Get semua menu yang bisa diakses
$accessibleMenus = PermissionHelper::getAccessibleMenus($userRole);
```

### 2. RoleMiddleware (`app/Http/Middleware/RoleMiddleware.php`)

Middleware untuk protect routes berdasarkan role.

**Cara Menggunakan di Routes:**

```php
// Single role
Route::get('/users', [UserController::class, 'index'])
    ->middleware(['auth', 'role:coordinator,ast_manager,manager,general_manager']);

// Multiple roles (gunakan koma untuk memisahkan)
Route::get('/machines', [MachineController::class, 'index'])
    ->middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager']);
```

### 3. User Model Methods

User model memiliki helper methods untuk check role dan permission:

```php
// Check role
$user->hasRole('coordinator');
$user->hasAnyRole(['coordinator', 'manager']);

// Check menu access
$user->canAccessMenu('users');

// Check role hierarchy
$user->isRoleHigherOrEqual('coordinator');
```

### 4. Navigation Menu Filtering

Navigation menu otomatis di-filter berdasarkan role user. Menu yang tidak bisa diakses tidak akan ditampilkan.

## Konfigurasi Akses Menu

Akses menu dikonfigurasi di `app/Helpers/PermissionHelper.php` pada property `$menuPermissions`.

**Format:**
```php
'menu_key' => [
    'allowed_roles' => ['role1', 'role2', ...], // atau ['all'] untuk semua role
    'min_level' => X // (opsional) minimum level yang diperlukan
]
```

**Contoh:**
```php
'users' => [
    'allowed_roles' => ['coordinator', 'ast_manager', 'manager', 'general_manager']
],
'activities' => [
    'allowed_roles' => ['all'] // Semua role bisa akses
],
```

## Menambahkan Menu Baru

1. **Tambahkan menu key ke `$menuPermissions` di `PermissionHelper.php`:**

```php
'new-menu' => [
    'allowed_roles' => ['group_leader', 'coordinator', 'ast_manager', 'manager', 'general_manager']
],
```

2. **Tambahkan `menu_key` ke menu di `navigation.blade.php`:**

```php
[
    'name' => 'New Menu',
    'route' => '/new-menu',
    'icon' => 'icon-name',
    'type' => 'single',
    'menu_key' => 'new-menu' // Tambahkan ini
]
```

3. **Jika perlu, protect route dengan middleware:**

```php
Route::get('/new-menu', [NewController::class, 'index'])
    ->middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager']);
```

## Menambahkan Permission Check di Controller

```php
use App\Helpers\PermissionHelper;

public function index()
{
    $userRole = auth()->user()->role ?? 'mekanik';
    
    if (!PermissionHelper::canAccessMenu($userRole, 'menu_key')) {
        abort(403, 'Unauthorized access.');
    }
    
    // Controller logic...
}
```

## Menambahkan Permission Check di Blade View

```blade
@if(auth()->user()->canAccessMenu('menu_key'))
    <!-- Content yang hanya bisa dilihat oleh role tertentu -->
@endif

@if(auth()->user()->hasRole('coordinator'))
    <!-- Content khusus coordinator -->
@endif
```

## Daftar Menu Key yang Tersedia

- `dashboard` - Dashboard (semua role)
- `location` - Location menu group
- `plants`, `processes`, `lines`, `room-erp` - Location submenus
- `machinary` - Machinary menu group
- `systems`, `groups`, `machine-types`, `brands`, `models`, `machine-erp`, `mutasi` - Machinary submenus
- `downtime` - Downtime menu group
- `problems`, `reasons`, `actions`, `downtime-erp2`, `work-orders` - Downtime submenus
- `users` - Users menu group
- `users-list`, `organizational-structure`, `activities` - Users submenus
- `preventive-maintenance` - Preventive Maintenance menu group
- `preventive-scheduling`, `preventive-controlling`, `preventive-monitoring`, `preventive-updating`, `preventive-reporting` - Preventive Maintenance submenus
- `predictive-maintenance` - Predictive Maintenance menu group
- `standards`, `predictive-scheduling`, `predictive-controlling`, `predictive-monitoring`, `predictive-updating`, `predictive-reporting` - Predictive Maintenance submenus
- `reports` - Reports menu group
- `mttr-mtbf`, `pareto-machine`, `summary-downtime`, `mechanic-performance`, `root-cause-analysis` - Reports submenus
- `part-erp` - Part ERP

## Catatan

- Menu yang tidak memiliki permission akan otomatis disembunyikan dari navigation
- Routes yang tidak di-protect dengan middleware masih bisa diakses langsung via URL
- Pastikan untuk protect routes penting dengan middleware `role`
- Permission check di controller dan view adalah opsional tapi direkomendasikan untuk keamanan tambahan


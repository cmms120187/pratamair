<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Downtime Alert Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk mengatur siapa saja yang akan menerima email alert
    | ketika terjadi downtime dengan kriteria tertentu.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    |
    | Threshold untuk mengirim alert berdasarkan durasi downtime (dalam menit)
    |
    */

    'duration_threshold' => env('DOWNTIME_ALERT_DURATION_THRESHOLD', 60), // Default: 60 menit

    /*
    |--------------------------------------------------------------------------
    | Critical Problems
    |--------------------------------------------------------------------------
    |
    | Daftar problem yang dianggap critical dan akan trigger alert
    | meskipun durasinya kurang dari threshold
    |
    */

    'critical_problems' => [
        'Motor Failure',
        'Safety Issue',
        'Electrical Failure',
        'Fire',
        'Emergency Stop',
        'Critical Breakdown',
    ],

    /*
    |--------------------------------------------------------------------------
    | Critical Machines
    |--------------------------------------------------------------------------
    |
    | Daftar ID mesin yang dianggap critical dan akan trigger alert
    | meskipun durasinya kurang dari threshold
    |
    */

    'critical_machines' => env('DOWNTIME_ALERT_CRITICAL_MACHINES', '') 
        ? explode(',', env('DOWNTIME_ALERT_CRITICAL_MACHINES')) 
        : [],

    /*
    |--------------------------------------------------------------------------
    | Notification Recipients
    |--------------------------------------------------------------------------
    |
    | Cara mengatur siapa yang akan menerima email alert:
    |
    | 1. By Role: Menggunakan role dari tabel users
    |    Contoh: ['manager', 'general_manager', 'coordinator']
    |
    | 2. By Email: Langsung spesifik email addresses
    |    Contoh: ['manager1@company.com', 'manager2@company.com']
    |
    | 3. By User ID: Spesifik user IDs dari tabel users
    |    Contoh: [1, 2, 3]
    |
    | 4. Mixed: Kombinasi role dan email
    |    Contoh: ['role:manager', 'email:admin@company.com']
    |
    | Priority: email > user_id > role
    |
    */

    'recipients' => [
        // Method 1: By Role (Recommended)
        'roles' => env('DOWNTIME_ALERT_ROLES', 'manager,general_manager,coordinator,ast_manager')
            ? explode(',', env('DOWNTIME_ALERT_ROLES'))
            : ['manager', 'general_manager', 'coordinator', 'ast_manager'],

        // Method 2: By Email (Override roles)
        'emails' => env('DOWNTIME_ALERT_EMAILS', '')
            ? explode(',', env('DOWNTIME_ALERT_EMAILS'))
            : [],

        // Method 3: By User ID (Override roles)
        'user_ids' => env('DOWNTIME_ALERT_USER_IDS', '')
            ? array_map('intval', explode(',', env('DOWNTIME_ALERT_USER_IDS')))
            : [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Alert
    |--------------------------------------------------------------------------
    |
    | Set true untuk enable alert, false untuk disable
    |
    */

    'enabled' => env('DOWNTIME_ALERT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Alert Types
    |--------------------------------------------------------------------------
    |
    | Jenis alert yang akan dikirim:
    | - duration: Alert jika duration > threshold
    | - critical_problem: Alert jika problem termasuk critical
    | - critical_machine: Alert jika machine termasuk critical
    |
    */

    'alert_types' => [
        'duration' => env('DOWNTIME_ALERT_TYPE_DURATION', true),
        'critical_problem' => env('DOWNTIME_ALERT_TYPE_CRITICAL_PROBLEM', true),
        'critical_machine' => env('DOWNTIME_ALERT_TYPE_CRITICAL_MACHINE', true),
    ],

];


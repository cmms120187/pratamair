<?php
/**
 * Script untuk fix migration di production
 * Jalankan sekali via browser atau CLI: php fix_migration.php
 * Hapus file ini setelah selesai!
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Fix Migration Script ===\n\n";

// 1. Hapus record migration yang gagal
$deleted = DB::table('migrations')
    ->where('migration', '2025_12_11_095930_add_work_hours_to_production_daily_grades_table')
    ->delete();

if ($deleted > 0) {
    echo "✓ Record migration yang gagal telah dihapus\n";
} else {
    echo "⚠ Record migration tidak ditemukan (mungkin sudah dihapus)\n";
}

// 2. Cek kolom yang sudah ada
echo "\n=== Cek Kolom yang Sudah Ada ===\n";
$columns = [
    'target_per_hour',
    'start_time',
    'end_time',
    'break_duration'
];

foreach ($columns as $column) {
    $exists = Schema::hasColumn('production_daily_grades', $column);
    echo ($exists ? "✓" : "✗") . " Kolom '{$column}': " . ($exists ? "Sudah ada" : "Belum ada") . "\n";
}

echo "\n=== Selesai ===\n";
echo "Sekarang jalankan: php artisan migrate\n";


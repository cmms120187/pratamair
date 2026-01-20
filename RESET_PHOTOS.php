<?php

/**
 * Script untuk Reset Semua Foto ke Database
 * 
 * Cara menggunakan:
 * 1. php artisan tinker
 * 2. Copy-paste isi file ini
 * 
 * ATAU
 * 
 * php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); require 'RESET_PHOTOS.php';"
 */

use App\Models\Photo;
use App\Models\Standard;
use App\Models\MachineErp;
use App\Models\MachineType;
use App\Models\Model;
use App\Models\User;
use App\Models\MaintenancePoint;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "  RESET ALL PHOTOS TO DATABASE\n";
echo "========================================\n\n";

echo "PERINGATAN: Script ini akan:\n";
echo "  1. Reset semua photo_id menjadi NULL\n";
echo "  2. Hapus semua data dari tabel photos\n";
echo "  3. Reset auto increment\n";
echo "  4. Kosongkan pivot table standard_standard_photo\n\n";
echo "File foto di storage TIDAK akan dihapus.\n\n";

echo "Apakah Anda yakin ingin melanjutkan? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "\nDibatalkan.\n";
    exit;
}

echo "\n=== Memulai Reset ===\n\n";

// 1. Reset photo_id di semua tabel
echo "1. Resetting photo_id di semua tabel...\n";

$standardsReset = Standard::whereNotNull('photo_id')->update(['photo_id' => null]);
echo "   ✓ Standards: {$standardsReset} records reset\n";

$machineErpReset = MachineErp::whereNotNull('photo_id')->update(['photo_id' => null]);
echo "   ✓ Machine ERP: {$machineErpReset} records reset\n";

$machineTypesReset = MachineType::whereNotNull('photo_id')->update(['photo_id' => null]);
echo "   ✓ Machine Types: {$machineTypesReset} records reset\n";

$modelsReset = Model::whereNotNull('photo_id')->update(['photo_id' => null]);
echo "   ✓ Models: {$modelsReset} records reset\n";

$usersReset = User::whereNotNull('photo_id')->update(['photo_id' => null]);
echo "   ✓ Users: {$usersReset} records reset\n";

$maintenancePointsReset = MaintenancePoint::whereNotNull('photo_id')->update(['photo_id' => null]);
echo "   ✓ Maintenance Points: {$maintenancePointsReset} records reset\n";

// 2. Hapus semua data dari tabel photos
echo "\n2. Menghapus semua data dari tabel photos...\n";
$totalPhotos = Photo::count();

// Disable foreign key checks untuk bisa truncate
DB::statement('SET FOREIGN_KEY_CHECKS = 0');
Photo::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS = 1');

echo "   ✓ {$totalPhotos} records dihapus dari tabel photos\n";

// 3. Reset auto increment
echo "\n3. Reset auto increment tabel photos...\n";
try {
    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    DB::statement('ALTER TABLE photos AUTO_INCREMENT = 1');
    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    echo "   ✓ Auto increment direset ke 1\n";
} catch (\Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // Pastikan di-enable kembali
    echo "   ⚠ Warning: " . $e->getMessage() . "\n";
}

// 4. Hapus pivot table standard_standard_photo
echo "\n4. Membersihkan pivot table standard_standard_photo...\n";
try {
    if (DB::getSchemaBuilder()->hasTable('standard_standard_photo')) {
        $pivotCount = DB::table('standard_standard_photo')->count();
        DB::table('standard_standard_photo')->truncate();
        echo "   ✓ {$pivotCount} records dihapus dari pivot table\n";
    } else {
        echo "   ℹ Pivot table tidak ditemukan\n";
    }
} catch (\Exception $e) {
    echo "   ⚠ Warning: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "  RESET SELESAI!\n";
echo "========================================\n\n";
echo "Semua photo_id sudah direset menjadi NULL\n";
echo "Tabel photos sudah dikosongkan\n";
echo "Auto increment sudah direset\n\n";
echo "CATATAN:\n";
echo "  - File foto di storage TIDAK dihapus\n";
echo "  - Sekarang Anda bisa upload ulang semua foto\n";
echo "  - Foto baru akan otomatis masuk ke database photos\n\n";


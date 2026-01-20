<?php

/**
 * Script untuk mengecek status foto di database
 * 
 * Jalankan dengan: php artisan tinker
 * Lalu copy-paste isi file ini
 * 
 * ATAU
 * 
 * php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); require 'CHECK_PHOTO_STATUS.php';"
 */

use App\Models\Photo;
use App\Models\Standard;
use App\Models\MachineErp;
use App\Models\MachineType;
use App\Models\Model;
use App\Models\User;
use App\Models\MaintenancePoint;
use App\Models\StandardPhoto;
use Illuminate\Support\Facades\DB;

echo "\n=== PHOTO DATABASE STATUS CHECK ===\n\n";

// 1. Total foto di tabel photos
$totalPhotos = Photo::count();
echo "1. Total foto di tabel photos: {$totalPhotos}\n";

// 2. Foto per related_type
echo "\n2. Foto per related_type:\n";
$photosByType = Photo::select('related_type', DB::raw('count(*) as count'))
    ->whereNotNull('related_type')
    ->groupBy('related_type')
    ->get();
foreach ($photosByType as $item) {
    echo "   - {$item->related_type}: {$item->count}\n";
}

// 3. Standards
echo "\n3. STANDARDS:\n";
$standardsWithPhotoId = Standard::whereNotNull('photo_id')->count();
$standardsWithPhoto = Standard::whereNotNull('photo')->count();
$standardsWithPhotosRelation = DB::table('standard_standard_photo')->distinct('standard_id')->count('standard_id');
$totalStandards = Standard::count();
echo "   - Total standards: {$totalStandards}\n";
echo "   - Dengan photo_id: {$standardsWithPhotoId}\n";
echo "   - Dengan photo (legacy): {$standardsWithPhoto}\n";
echo "   - Dengan photos relation: {$standardsWithPhotosRelation}\n";

// 4. Machine ERP
echo "\n4. MACHINE ERP:\n";
$machineErpWithPhotoId = MachineErp::whereNotNull('photo_id')->count();
$machineErpWithPhoto = MachineErp::whereNotNull('photo')->count();
$totalMachineErp = MachineErp::count();
echo "   - Total machine ERP: {$totalMachineErp}\n";
echo "   - Dengan photo_id: {$machineErpWithPhotoId}\n";
echo "   - Dengan photo (legacy): {$machineErpWithPhoto}\n";

// 5. Machine Types
echo "\n5. MACHINE TYPES:\n";
$machineTypesWithPhotoId = MachineType::whereNotNull('photo_id')->count();
$machineTypesWithPhoto = MachineType::whereNotNull('photo')->count();
$totalMachineTypes = MachineType::count();
echo "   - Total machine types: {$totalMachineTypes}\n";
echo "   - Dengan photo_id: {$machineTypesWithPhotoId}\n";
echo "   - Dengan photo (legacy): {$machineTypesWithPhoto}\n";

// 6. Models
echo "\n6. MODELS:\n";
$modelsWithPhotoId = Model::whereNotNull('photo_id')->count();
$modelsWithPhoto = Model::whereNotNull('photo')->count();
$totalModels = Model::count();
echo "   - Total models: {$totalModels}\n";
echo "   - Dengan photo_id: {$modelsWithPhotoId}\n";
echo "   - Dengan photo (legacy): {$modelsWithPhoto}\n";

// 7. Users
echo "\n7. USERS:\n";
$usersWithPhotoId = User::whereNotNull('photo_id')->count();
$usersWithPhoto = User::whereNotNull('photo')->count();
$totalUsers = User::count();
echo "   - Total users: {$totalUsers}\n";
echo "   - Dengan photo_id: {$usersWithPhotoId}\n";
echo "   - Dengan photo (legacy): {$usersWithPhoto}\n";

// 8. Maintenance Points
echo "\n8. MAINTENANCE POINTS:\n";
$maintenancePointsWithPhotoId = MaintenancePoint::whereNotNull('photo_id')->count();
$maintenancePointsWithPhoto = MaintenancePoint::whereNotNull('photo')->count();
$totalMaintenancePoints = MaintenancePoint::count();
echo "   - Total maintenance points: {$totalMaintenancePoints}\n";
echo "   - Dengan photo_id: {$maintenancePointsWithPhotoId}\n";
echo "   - Dengan photo (legacy): {$maintenancePointsWithPhoto}\n";

// 9. Standard Photos (Legacy)
echo "\n9. STANDARD PHOTOS (Legacy):\n";
$totalStandardPhotos = StandardPhoto::count();
echo "   - Total standard photos: {$totalStandardPhotos}\n";

// 10. Summary
echo "\n=== SUMMARY ===\n";
$totalWithPhotoId = $standardsWithPhotoId + $machineErpWithPhotoId + $machineTypesWithPhotoId + 
                    $modelsWithPhotoId + $usersWithPhotoId + $maintenancePointsWithPhotoId;
$totalWithPhoto = $standardsWithPhoto + $machineErpWithPhoto + $machineTypesWithPhoto + 
                  $modelsWithPhoto + $usersWithPhoto + $maintenancePointsWithPhoto;

echo "Total records dengan photo_id: {$totalWithPhotoId}\n";
echo "Total records dengan photo (legacy): {$totalWithPhoto}\n";
echo "Total foto di database: {$totalPhotos}\n";

if ($totalPhotos > 0 && $totalWithPhotoId > 0) {
    $percentage = round(($totalWithPhotoId / ($totalWithPhotoId + $totalWithPhoto)) * 100, 2);
    echo "\nPersentase yang sudah menggunakan photo_id: {$percentage}%\n";
}

echo "\n=== REKOMENDASI ===\n";
if ($totalPhotos == 0) {
    echo "❌ BELUM ADA FOTO DI DATABASE!\n";
    echo "   → Jalankan: php artisan db:seed --class=MigrateAllPhotosToDatabaseSeeder\n";
} elseif ($totalWithPhoto > 0) {
    echo "⚠️  MASIH ADA FOTO YANG BELUM DIMIGRASI!\n";
    echo "   → Jalankan: php artisan db:seed --class=MigrateAllPhotosToDatabaseSeeder\n";
    echo "   → Atau migrasi manual untuk foto yang belum masuk\n";
} else {
    echo "✅ SEMUA FOTO SUDAH MASUK DATABASE!\n";
}

echo "\n";


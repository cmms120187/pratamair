<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Photo;
use App\Models\Standard;
use App\Models\MachineErp;
use App\Models\MachineType;
use App\Models\Model;
use App\Models\User;
use App\Models\MaintenancePoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResetAllPhotosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * PERINGATAN: Seeder ini akan menghapus semua data foto dari database
     * dan mereset semua photo_id menjadi NULL.
     * 
     * File foto di storage TIDAK akan dihapus (untuk keamanan).
     * Jika ingin menghapus file juga, jalankan perintah terpisah.
     */
    public function run(): void
    {
        $this->command->info('=== RESET ALL PHOTOS TO DATABASE ===');
        $this->command->warn('PERINGATAN: Seeder ini akan menghapus semua data foto dari database!');
        
        if (!$this->command->confirm('Apakah Anda yakin ingin melanjutkan?', false)) {
            $this->command->info('Dibatalkan.');
            return;
        }
        
        // 1. Reset photo_id di semua tabel
        $this->command->info("\n1. Resetting photo_id di semua tabel...");
        
        $standardsReset = Standard::whereNotNull('photo_id')->update(['photo_id' => null]);
        $this->command->info("   - Standards: {$standardsReset} records reset");
        
        $machineErpReset = MachineErp::whereNotNull('photo_id')->update(['photo_id' => null]);
        $this->command->info("   - Machine ERP: {$machineErpReset} records reset");
        
        $machineTypesReset = MachineType::whereNotNull('photo_id')->update(['photo_id' => null]);
        $this->command->info("   - Machine Types: {$machineTypesReset} records reset");
        
        $modelsReset = Model::whereNotNull('photo_id')->update(['photo_id' => null]);
        $this->command->info("   - Models: {$modelsReset} records reset");
        
        $usersReset = User::whereNotNull('photo_id')->update(['photo_id' => null]);
        $this->command->info("   - Users: {$usersReset} records reset");
        
        $maintenancePointsReset = MaintenancePoint::whereNotNull('photo_id')->update(['photo_id' => null]);
        $this->command->info("   - Maintenance Points: {$maintenancePointsReset} records reset");
        
        // 2. Hapus semua data dari tabel photos
        $this->command->info("\n2. Menghapus semua data dari tabel photos...");
        $totalPhotos = Photo::count();
        
        // Disable foreign key checks untuk bisa truncate
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Photo::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        
        $this->command->info("   - {$totalPhotos} records dihapus dari tabel photos");
        
        // 3. Reset auto increment
        $this->command->info("\n3. Reset auto increment tabel photos...");
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement('ALTER TABLE photos AUTO_INCREMENT = 1');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $this->command->info("   - Auto increment direset ke 1");
        
        // 4. Hapus pivot table standard_standard_photo (jika ada)
        $this->command->info("\n4. Membersihkan pivot table standard_standard_photo...");
        if (DB::getSchemaBuilder()->hasTable('standard_standard_photo')) {
            $pivotCount = DB::table('standard_standard_photo')->count();
            DB::table('standard_standard_photo')->truncate();
            $this->command->info("   - {$pivotCount} records dihapus dari pivot table");
        } else {
            $this->command->info("   - Pivot table tidak ditemukan");
        }
        
        $this->command->info("\n=== RESET SELESAI ===");
        $this->command->info("Semua photo_id sudah direset menjadi NULL");
        $this->command->info("Tabel photos sudah dikosongkan");
        $this->command->warn("\nCATATAN: File foto di storage TIDAK dihapus");
        $this->command->warn("Jika ingin menghapus file juga, jalankan perintah terpisah");
        $this->command->info("\nSekarang Anda bisa upload ulang semua foto dan akan masuk ke database photos.");
    }
}

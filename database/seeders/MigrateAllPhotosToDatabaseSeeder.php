<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Photo;
use App\Models\Standard;
use App\Models\StandardPhoto;
use App\Models\MachineType;
use App\Models\MaintenancePoint;
use App\Models\User;
use App\Models\MachineErp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MigrateAllPhotosToDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting migration of all photos to database...');
        
        $totalMigrated = 0;
        
        // 1. Migrate photos from public/images (except logo_tpm.png)
        $totalMigrated += $this->migratePublicImages();
        
        // 2. Migrate photos from storage/app/public/standards
        $totalMigrated += $this->migrateStoragePhotos('standards', 'standard');
        
        // 3. Migrate photos from storage/app/public/machine-types
        $totalMigrated += $this->migrateStoragePhotos('machine-types', 'machine_type');
        
        // 4. Migrate photos from storage/app/public/maintenance-points
        $totalMigrated += $this->migrateStoragePhotos('maintenance-points', 'maintenance_point');
        
        // 5. Migrate photos from storage/app/public/users
        $totalMigrated += $this->migrateStoragePhotos('users', 'user');
        
        // 6. Migrate photos from storage/app/public/activities
        $totalMigrated += $this->migrateStoragePhotos('activities', 'activity');
        
        // 7. Migrate photos from storage/app/public/machine-erp (if exists)
        $totalMigrated += $this->migrateStoragePhotos('machine-erp', 'machine_erp');
        
        // 8. Migrate StandardPhoto to Photo
        $totalMigrated += $this->migrateStandardPhotos();
        
        // 9. Migrate Standard.photo field to Photo
        $totalMigrated += $this->migrateStandardPhotoField();
        
        $this->command->info("\n=== Migration Summary ===");
        $this->command->info("Total photos migrated: {$totalMigrated}");
        $this->command->info("\nMigration completed!");
    }

    /**
     * Migrate photos from public/images (except logo_tpm.png)
     */
    private function migratePublicImages(): int
    {
        $this->command->info("\nMigrating photos from public/images...");
        $count = 0;
        
        $publicImagesPath = public_path('images');
        if (!is_dir($publicImagesPath)) {
            $this->command->warn('public/images directory does not exist');
            return 0;
        }
        
        $files = File::files($publicImagesPath);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        foreach ($files as $file) {
            $filename = $file->getFilename();
            
            // Skip logo_tpm.png (for icon/welcome page)
            if ($filename === 'logo_tpm.png') {
                continue;
            }
            
            $extension = strtolower($file->getExtension());
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }
            
            // Try to find related Standard by matching filename
            $standard = $this->findStandardByPhotoFilename($filename);
            
            if ($standard) {
                // Copy file to storage
                $newPath = 'standards/' . uniqid() . '_' . time() . '.' . $extension;
                Storage::disk('public')->put($newPath, File::get($file->getPathname()));
                
                // Get file info
                $fileSize = $file->getSize();
                $imageInfo = @getimagesize($file->getPathname());
                $mimeType = $imageInfo ? $imageInfo['mime'] : 'image/' . $extension;
                $width = $imageInfo ? $imageInfo[0] : null;
                $height = $imageInfo ? $imageInfo[1] : null;
                
                // Create photo record
                $photo = Photo::create([
                    'original_filename' => $filename,
                    'stored_filename' => basename($newPath),
                    'file_path' => $newPath,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'width' => $width,
                    'height' => $height,
                    'related_type' => 'standard',
                    'related_id' => $standard->id,
                    'description' => "Photo for {$standard->name}",
                ]);
                
                // Link to StandardPhoto if exists
                $standardPhoto = StandardPhoto::where('photo_path', 'images/' . $filename)
                    ->orWhere('photo_path', 'like', '%' . $filename)
                    ->first();
                
                if ($standardPhoto) {
                    // Update StandardPhoto to use Photo ID (we'll add photo_id column later if needed)
                    // For now, just create the link
                }
                
                $count++;
                $this->command->info("Migrated: {$filename} -> Standard: {$standard->name}");
            } else {
                // If no standard found, still migrate but without related_id
                $newPath = 'standards/' . uniqid() . '_' . time() . '.' . $extension;
                Storage::disk('public')->put($newPath, File::get($file->getPathname()));
                
                $fileSize = $file->getSize();
                $imageInfo = @getimagesize($file->getPathname());
                $mimeType = $imageInfo ? $imageInfo['mime'] : 'image/' . $extension;
                $width = $imageInfo ? $imageInfo[0] : null;
                $height = $imageInfo ? $imageInfo[1] : null;
                
                Photo::create([
                    'original_filename' => $filename,
                    'stored_filename' => basename($newPath),
                    'file_path' => $newPath,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'width' => $width,
                    'height' => $height,
                    'related_type' => 'standard',
                    'description' => "Photo from public/images: {$filename}",
                ]);
                
                $count++;
                $this->command->info("Migrated: {$filename} (no related standard found)");
            }
        }
        
        return $count;
    }

    /**
     * Migrate photos from storage/app/public/{directory}
     */
    private function migrateStoragePhotos(string $directory, string $relatedType): int
    {
        $this->command->info("\nMigrating photos from storage/app/public/{$directory}...");
        $count = 0;
        
        if (!Storage::disk('public')->exists($directory)) {
            return 0;
        }
        
        $files = Storage::disk('public')->files($directory);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        foreach ($files as $filePath) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }
            
            // Check if already migrated
            $existing = Photo::where('file_path', $filePath)->first();
            if ($existing) {
                continue;
            }
            
            // Get file info
            $fileSize = Storage::disk('public')->size($filePath);
            $mimeType = Storage::disk('public')->mimeType($filePath);
            $imageInfo = @getimagesize(Storage::disk('public')->path($filePath));
            $width = $imageInfo ? $imageInfo[0] : null;
            $height = $imageInfo ? $imageInfo[1] : null;
            
            // Try to find related record
            $relatedId = $this->findRelatedId($filePath, $relatedType);
            
            $photo = Photo::create([
                'original_filename' => basename($filePath),
                'stored_filename' => basename($filePath),
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'width' => $width,
                'height' => $height,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'description' => "Photo from {$directory}",
            ]);
            
            // Update related model's photo_id if found
            if ($relatedId) {
                $this->updateRelatedPhotoId($relatedType, $relatedId, $photo->id, $filePath);
            }
            
            $count++;
            $this->command->info("Migrated: {$filePath}");
        }
        
        return $count;
    }

    /**
     * Migrate StandardPhoto to Photo
     */
    private function migrateStandardPhotos(): int
    {
        $this->command->info("\nMigrating StandardPhoto to Photo...");
        $count = 0;
        
        $standardPhotos = StandardPhoto::all();
        
        foreach ($standardPhotos as $standardPhoto) {
            // Check if already migrated
            $existing = Photo::where('file_path', $standardPhoto->photo_path)
                ->where('related_type', 'standard')
                ->where('related_id', $standardPhoto->standard_id)
                ->first();
            
            if ($existing) {
                continue;
            }
            
            if (!Storage::disk('public')->exists($standardPhoto->photo_path)) {
                // Try public/images path
                $publicPath = 'images/' . basename($standardPhoto->photo_path);
                if (!file_exists(public_path($publicPath))) {
                    $this->command->warn("Photo not found: {$standardPhoto->photo_path}");
                    continue;
                }
            }
            
            $filePath = $standardPhoto->photo_path;
            if (strpos($filePath, 'images/') === 0) {
                // File is in public/images, need to copy to storage
                $publicPath = public_path($filePath);
                if (file_exists($publicPath)) {
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $newPath = 'standards/' . uniqid() . '_' . time() . '.' . $extension;
                    Storage::disk('public')->put($newPath, File::get($publicPath));
                    $filePath = $newPath;
                }
            }
            
            $fileSize = Storage::disk('public')->exists($filePath) ? Storage::disk('public')->size($filePath) : 0;
            $mimeType = Storage::disk('public')->exists($filePath) ? Storage::disk('public')->mimeType($filePath) : 'image/jpeg';
            $imageInfo = Storage::disk('public')->exists($filePath) ? @getimagesize(Storage::disk('public')->path($filePath)) : false;
            $width = $imageInfo ? $imageInfo[0] : null;
            $height = $imageInfo ? $imageInfo[1] : null;
            
            Photo::create([
                'original_filename' => basename($standardPhoto->photo_path),
                'stored_filename' => basename($filePath),
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'width' => $width,
                'height' => $height,
                'related_type' => 'standard',
                'related_id' => $standardPhoto->standard_id,
                'description' => $standardPhoto->name ?? "Photo for standard",
            ]);
            
            $count++;
            $this->command->info("Migrated StandardPhoto: {$standardPhoto->photo_path}");
        }
        
        return $count;
    }

    /**
     * Migrate Standard.photo field to Photo
     */
    private function migrateStandardPhotoField(): int
    {
        $this->command->info("\nMigrating Standard.photo field to Photo...");
        $count = 0;
        
        $standards = Standard::whereNotNull('photo')->get();
        
        foreach ($standards as $standard) {
            // Check if already migrated
            $existing = Photo::where('file_path', $standard->photo)
                ->where('related_type', 'standard')
                ->where('related_id', $standard->id)
                ->first();
            
            if ($existing) {
                continue;
            }
            
            $filePath = $standard->photo;
            $actualPath = $filePath;
            
            // Check if file exists in storage
            if (!Storage::disk('public')->exists($filePath)) {
                // Try public/images path
                $publicPath = 'images/' . basename($filePath);
                if (file_exists(public_path($publicPath))) {
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $newPath = 'standards/' . uniqid() . '_' . time() . '.' . $extension;
                    Storage::disk('public')->put($newPath, File::get(public_path($publicPath)));
                    $actualPath = $newPath;
                } else {
                    $this->command->warn("Photo not found: {$filePath}");
                    continue;
                }
            }
            
            $fileSize = Storage::disk('public')->size($actualPath);
            $mimeType = Storage::disk('public')->mimeType($actualPath);
            $imageInfo = @getimagesize(Storage::disk('public')->path($actualPath));
            $width = $imageInfo ? $imageInfo[0] : null;
            $height = $imageInfo ? $imageInfo[1] : null;
            
            $photo = Photo::create([
                'original_filename' => basename($filePath),
                'stored_filename' => basename($actualPath),
                'file_path' => $actualPath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'width' => $width,
                'height' => $height,
                'related_type' => 'standard',
                'related_id' => $standard->id,
                'description' => "Photo for {$standard->name}",
            ]);
            
            // Update standard.photo_id
            $standard->photo_id = $photo->id;
            $standard->save();
            
            $count++;
            $this->command->info("Migrated Standard.photo: {$filePath} -> {$standard->name}");
        }
        
        return $count;
    }

    /**
     * Find Standard by photo filename
     */
    private function findStandardByPhotoFilename(string $filename): ?Standard
    {
        // Try exact match with photo field
        $standard = Standard::where('photo', 'images/' . $filename)
            ->orWhere('photo', 'like', '%' . $filename)
            ->first();
        
        if ($standard) {
            return $standard;
        }
        
        // Try to match by name (remove extension and match)
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $standard = Standard::where('name', 'like', '%' . $nameWithoutExt . '%')
            ->orWhere('reference_name', 'like', '%' . $nameWithoutExt . '%')
            ->first();
        
        return $standard;
    }

    /**
     * Find related ID based on file path and type
     */
    private function findRelatedId(string $filePath, string $relatedType): ?int
    {
        $filename = basename($filePath);
        
        switch ($relatedType) {
            case 'machine_type':
                // Try to find by photo field
                $machineType = MachineType::where('photo', $filePath)
                    ->orWhere('photo', 'like', '%' . $filename)
                    ->first();
                return $machineType ? $machineType->id : null;
                
            case 'maintenance_point':
                $maintenancePoint = MaintenancePoint::where('photo', $filePath)
                    ->orWhere('photo', 'like', '%' . $filename)
                    ->first();
                return $maintenancePoint ? $maintenancePoint->id : null;
                
            case 'user':
                $user = User::where('photo', $filePath)
                    ->orWhere('photo', 'like', '%' . $filename)
                    ->first();
                return $user ? $user->id : null;
                
            case 'machine_erp':
                $machineErp = MachineErp::where('photo', $filePath)
                    ->orWhere('photo', 'like', '%' . $filename)
                    ->first();
                return $machineErp ? $machineErp->id : null;
                
            default:
                return null;
        }
    }

    /**
     * Update related model's photo_id
     */
    private function updateRelatedPhotoId(string $relatedType, int $relatedId, int $photoId, string $filePath): void
    {
        switch ($relatedType) {
            case 'machine_type':
                $model = MachineType::find($relatedId);
                break;
            case 'maintenance_point':
                $model = MaintenancePoint::find($relatedId);
                break;
            case 'user':
                $model = User::find($relatedId);
                break;
            case 'machine_erp':
                $model = MachineErp::find($relatedId);
                break;
            case 'standard':
                $model = Standard::find($relatedId);
                break;
            default:
                return;
        }
        
        if ($model && ($model->photo === $filePath || str_contains($model->photo ?? '', basename($filePath)))) {
            $model->photo_id = $photoId;
            $model->save();
        }
    }
}

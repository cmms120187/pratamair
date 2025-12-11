<?php

namespace App\Console\Commands;

use App\Models\Standard;
use App\Models\StandardPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStandardPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'standards:migrate-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing standard photos from photo column to standard_photos table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of standard photos...');

        $standards = Standard::whereNotNull('photo')->get();
        $count = 0;

        foreach ($standards as $standard) {
            // Check if photo already exists in standard_photos
            $existingPhoto = StandardPhoto::where('photo_path', $standard->photo)
                ->where('standard_id', $standard->id)
                ->first();

            if (!$existingPhoto && Storage::disk('public')->exists($standard->photo)) {
                StandardPhoto::create([
                    'standard_id' => $standard->id,
                    'photo_path' => $standard->photo,
                    'name' => $standard->name . ' - Photo',
                ]);
                $count++;
                $this->info("Migrated photo for standard: {$standard->name}");
            }
        }

        $this->info("Migration completed! {$count} photos migrated.");
        return 0;
    }
}

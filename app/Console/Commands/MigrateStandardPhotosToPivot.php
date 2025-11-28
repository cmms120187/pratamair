<?php

namespace App\Console\Commands;

use App\Models\Standard;
use App\Models\StandardPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateStandardPhotosToPivot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'standards:migrate-photos-to-pivot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate standard photos from standard_id column to pivot table for many-to-many relationship';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of standard photos to pivot table...');

        $photos = StandardPhoto::whereNotNull('standard_id')->get();
        $count = 0;

        foreach ($photos as $photo) {
            // Check if relationship already exists in pivot table
            $exists = DB::table('standard_standard_photo')
                ->where('standard_id', $photo->standard_id)
                ->where('standard_photo_id', $photo->id)
                ->exists();

            if (!$exists) {
                DB::table('standard_standard_photo')->insert([
                    'standard_id' => $photo->standard_id,
                    'standard_photo_id' => $photo->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
                $standard = Standard::find($photo->standard_id);
                $this->info("Migrated photo '{$photo->name}' to standard: {$standard->name}");
            }
        }

        $this->info("Migration completed! {$count} photo-standard relationships migrated to pivot table.");
        return 0;
    }
}

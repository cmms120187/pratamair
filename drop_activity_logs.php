<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! Schema::hasTable('activity_logs')) {
    echo "Table activity_logs does not exist.\n";
    exit(0);
}

$backup = 'activity_logs_backup_'.date('Ymd_His');

try {
    // Create structure copy
    DB::statement("CREATE TABLE `{$backup}` LIKE `activity_logs`");
    // Copy data
    DB::statement("INSERT INTO `{$backup}` SELECT * FROM `activity_logs`");

    // Disable foreign key checks, drop table, re-enable
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    DB::statement('DROP TABLE `activity_logs`');
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    echo "Backup created: {$backup}\n";
    echo "Table activity_logs dropped.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

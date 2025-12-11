<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$currentYear = now()->year;

// Cek semua schedule dan assigned_to mereka
$schedules = \App\Models\PredictiveMaintenanceSchedule::where('status', 'active')
    ->whereYear('start_date', $currentYear)
    ->with('assignedUser')
    ->get();

echo "Schedule aktif tahun {$currentYear}:\n";
echo "Total: {$schedules->count()}\n\n";

$assignedToCounts = [];
foreach ($schedules as $schedule) {
    $assignedTo = $schedule->assigned_to;
    if (!isset($assignedToCounts[$assignedTo])) {
        $assignedToCounts[$assignedTo] = 0;
    }
    $assignedToCounts[$assignedTo]++;
}

echo "Distribusi assigned_to:\n";
foreach ($assignedToCounts as $userId => $count) {
    if ($userId) {
        $user = \App\Models\User::find($userId);
        $userName = $user ? $user->name . ' (NIK: ' . ($user->nik ?? '-') . ')' : "User ID {$userId}";
        echo "  User ID {$userId} ({$userName}): {$count} schedule\n";
    } else {
        echo "  NULL (tidak ada assigned_to): {$count} schedule\n";
    }
}


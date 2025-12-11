<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use App\Models\DowntimeErp2;
use App\Models\MachineErp;
use App\Models\RoomErp;

class UpdateKodeRoomForExistingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:kode-room';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update kode_room for existing data in activities, downtime_erp2, and machine_erp tables based on matching RoomERP data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update kode_room for existing data...');
        
        // Get all RoomERP data for matching
        $roomErps = RoomErp::whereNotNull('kode_room')
            ->where('kode_room', '!=', '')
            ->get();
        
        $this->info("Found {$roomErps->count()} RoomERP records with kode_room");
        
        // Update Activities
        $this->updateActivities($roomErps);
        
        // Update DowntimeErp2
        $this->updateDowntimeErp2($roomErps);
        
        // Update MachineErp
        $this->updateMachineErp($roomErps);
        
        $this->info('Update completed!');
        
        return Command::SUCCESS;
    }
    
    private function updateActivities($roomErps)
    {
        $this->info("\n=== Updating Activities ===");
        
        $activities = Activity::where(function($query) {
            $query->whereNull('kode_room')
                  ->orWhere('kode_room', '');
        })
        ->whereNotNull('plant')
        ->whereNotNull('process')
        ->whereNotNull('line')
        ->whereNotNull('room_name')
        ->get();
        
        $this->info("Found {$activities->count()} activities to update");
        
        $updated = 0;
        foreach ($activities as $activity) {
            $matched = $this->matchRoomErp(
                $roomErps,
                $activity->plant,
                $activity->process,
                $activity->line,
                $activity->room_name
            );
            
            if ($matched) {
                $activity->kode_room = $matched->kode_room;
                $activity->save();
                $updated++;
            }
        }
        
        $this->info("Updated {$updated} activities");
    }
    
    private function updateDowntimeErp2($roomErps)
    {
        $this->info("\n=== Updating DowntimeErp2 ===");
        
        $downtimes = DowntimeErp2::where(function($query) {
            $query->whereNull('kode_room')
                  ->orWhere('kode_room', '');
        })
        ->whereNotNull('plant')
        ->whereNotNull('process')
        ->whereNotNull('line')
        ->whereNotNull('roomName')
        ->get();
        
        $this->info("Found {$downtimes->count()} downtime records to update");
        
        $updated = 0;
        foreach ($downtimes as $downtime) {
            $matched = $this->matchRoomErp(
                $roomErps,
                $downtime->plant,
                $downtime->process,
                $downtime->line,
                $downtime->roomName
            );
            
            if ($matched) {
                $downtime->kode_room = $matched->kode_room;
                $downtime->save();
                $updated++;
            }
        }
        
        $this->info("Updated {$updated} downtime records");
    }
    
    private function updateMachineErp($roomErps)
    {
        $this->info("\n=== Updating MachineErp ===");
        
        $machines = MachineErp::where(function($query) {
            $query->whereNull('kode_room')
                  ->orWhere('kode_room', '');
        })
        ->whereNotNull('plant_name')
        ->whereNotNull('process_name')
        ->whereNotNull('line_name')
        ->whereNotNull('room_name')
        ->get();
        
        $this->info("Found {$machines->count()} machine records to update");
        
        $updated = 0;
        foreach ($machines as $machine) {
            $matched = $this->matchRoomErp(
                $roomErps,
                $machine->plant_name,
                $machine->process_name,
                $machine->line_name,
                $machine->room_name
            );
            
            if ($matched) {
                $machine->kode_room = $matched->kode_room;
                $machine->save();
                $updated++;
            }
        }
        
        $this->info("Updated {$updated} machine records");
    }
    
    /**
     * Match RoomERP based on plant, process, line, and room name
     */
    private function matchRoomErp($roomErps, $plant, $process, $line, $roomName)
    {
        // Try exact match first
        foreach ($roomErps as $roomErp) {
            if (trim($roomErp->plant_name ?? '') === trim($plant ?? '') &&
                trim($roomErp->process_name ?? '') === trim($process ?? '') &&
                trim($roomErp->line_name ?? '') === trim($line ?? '') &&
                trim($roomErp->name ?? '') === trim($roomName ?? '')) {
                return $roomErp;
            }
        }
        
        // Try case-insensitive match
        foreach ($roomErps as $roomErp) {
            if (strtolower(trim($roomErp->plant_name ?? '')) === strtolower(trim($plant ?? '')) &&
                strtolower(trim($roomErp->process_name ?? '')) === strtolower(trim($process ?? '')) &&
                strtolower(trim($roomErp->line_name ?? '')) === strtolower(trim($line ?? '')) &&
                strtolower(trim($roomErp->name ?? '')) === strtolower(trim($roomName ?? ''))) {
                return $roomErp;
            }
        }
        
        // Try partial match (if room name matches and at least 2 other fields match)
        foreach ($roomErps as $roomErp) {
            $matches = 0;
            if (strtolower(trim($roomErp->plant_name ?? '')) === strtolower(trim($plant ?? ''))) $matches++;
            if (strtolower(trim($roomErp->process_name ?? '')) === strtolower(trim($process ?? ''))) $matches++;
            if (strtolower(trim($roomErp->line_name ?? '')) === strtolower(trim($line ?? ''))) $matches++;
            if (strtolower(trim($roomErp->name ?? '')) === strtolower(trim($roomName ?? ''))) $matches++;
            
            if ($matches >= 3) {
                return $roomErp;
            }
        }
        
        return null;
    }
}
